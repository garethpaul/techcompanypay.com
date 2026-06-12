'use strict';

var assert = require('assert');
var fs = require('fs');
var vm = require('vm');

var source = fs.readFileSync(__dirname + '/../assets/app.js', 'utf8');

function deferred() {
  var resolve;
  var reject;
  var promise = new Promise(function (resolvePromise, rejectPromise) {
    resolve = resolvePromise;
    reject = rejectPromise;
  });
  return {promise: promise, resolve: resolve, reject: reject};
}

function createHarness(options) {
  var submitHandler = null;
  var timeoutHandler = null;
  var elements = {
    searchform: {
      action: 'find.php',
      addEventListener: function (event, handler) {
        if (event === 'submit') {
          submitHandler = handler;
        }
      }
    },
    search_results: {textContent: '', innerHTML: ''},
    search_term: {value: options.company || ''},
    city: {value: options.city || ''},
    submitbsearch: {disabled: false}
  };

  function FakeAbortController() {
    this.signal = {};
    this.aborted = false;
  }
  FakeAbortController.prototype.abort = function () {
    this.aborted = true;
  };

  var windowObject = {
    fetch: options.fetch,
    URLSearchParams: URLSearchParams,
    AbortController: FakeAbortController,
    setTimeout: function (handler) {
      timeoutHandler = handler;
      return 1;
    },
    clearTimeout: function () {
      timeoutHandler = null;
    }
  };

  vm.runInNewContext(source, {
    document: {
      getElementById: function (id) {
        return elements[id] || null;
      }
    },
    window: windowObject
  });

  return {
    elements: elements,
    submit: function () {
      var prevented = false;
      submitHandler({
        preventDefault: function () {
          prevented = true;
        }
      });
      return prevented;
    },
    timeout: function () {
      if (timeoutHandler) {
        timeoutHandler();
      }
    }
  };
}

function flushPromises() {
  return new Promise(function (resolve) {
    setImmediate(resolve);
  });
}

async function run() {
  var requests = [];
  var harness = createHarness({
    company: '',
    city: '',
    fetch: function (url, options) {
      var request = deferred();
      requests.push({url: url, options: options, response: request});
      return request.promise;
    }
  });

  harness.elements.search_term.value = 'A'.repeat(120);
  harness.elements.city.value = 'San Francisco';
  assert.strictEqual(harness.submit(), true, 'modern search should prevent native submission');
  assert.strictEqual(requests[0].url, 'find.php');
  assert.strictEqual(requests[0].options.method, 'POST');
  assert.strictEqual(requests[0].options.body.get('search_term').length, 100);
  assert.strictEqual(requests[0].options.body.get('city'), 'San Francisco');
  assert.strictEqual(harness.elements.submitbsearch.disabled, true);

  harness.elements.search_term.value = 'OpenAI';
  harness.submit();
  requests[1].response.resolve({
    ok: true,
    text: function () {
      return Promise.resolve('<p>New result</p>');
    }
  });
  await flushPromises();
  await flushPromises();
  assert.strictEqual(harness.elements.search_results.innerHTML, '<p>New result</p>');
  assert.strictEqual(harness.elements.submitbsearch.disabled, false);

  requests[0].response.resolve({
    ok: true,
    text: function () {
      return Promise.resolve('<p>Stale result</p>');
    }
  });
  await flushPromises();
  await flushPromises();
  assert.strictEqual(harness.elements.search_results.innerHTML, '<p>New result</p>', 'stale responses must not overwrite newer results');

  var failedHarness = createHarness({
    fetch: function () {
      return Promise.reject(new Error('offline'));
    }
  });
  failedHarness.submit();
  await flushPromises();
  assert.strictEqual(failedHarness.elements.search_results.textContent, 'Search is temporarily unavailable. Please try again.');
  assert.strictEqual(failedHarness.elements.submitbsearch.disabled, false);

  var timedOutRequest = deferred();
  var timedOutHarness = createHarness({
    fetch: function () {
      return timedOutRequest.promise;
    }
  });
  timedOutHarness.submit();
  timedOutHarness.timeout();
  timedOutRequest.reject({name: 'AbortError'});
  await flushPromises();
  assert.strictEqual(timedOutHarness.elements.search_results.textContent, 'Search is temporarily unavailable. Please try again.');
  assert.strictEqual(timedOutHarness.elements.submitbsearch.disabled, false);

  var fallbackHarness = createHarness({fetch: undefined});
  assert.strictEqual(fallbackHarness.submit(), false, 'unsupported browsers should keep the native form submission');

  var cityOnlyRequests = [];
  createHarness({
    city: 'New York',
    fetch: function (url, options) {
      cityOnlyRequests.push({url: url, options: options});
      return deferred().promise;
    }
  });
  assert.strictEqual(cityOnlyRequests.length, 1, 'prefilled city-only links should search automatically');
  assert.strictEqual(cityOnlyRequests[0].url, 'find.php');
  assert.strictEqual(cityOnlyRequests[0].options.body.get('search_term'), '');
  assert.strictEqual(cityOnlyRequests[0].options.body.get('city'), 'New York');

  console.log('local search behavior checks passed');
}

run().catch(function (error) {
  console.error(error.stack || error);
  process.exit(1);
});
