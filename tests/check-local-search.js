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

function htmlResponse(html, contentType) {
  return {
    ok: true,
    headers: {
      get: function (name) {
        return name.toLowerCase() === 'content-type' ? (contentType || 'text/html; charset=UTF-8') : null;
      }
    },
    text: function () {
      return Promise.resolve(html);
    }
  };
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
    AbortController: options.abortController === false ? undefined : FakeAbortController,
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
  requests[1].response.resolve(htmlResponse('<p>New result</p>', ' Text/HTML ; charset=UTF-8 '));
  await flushPromises();
  await flushPromises();
  assert.strictEqual(harness.elements.search_results.innerHTML, '<p>New result</p>');
  assert.strictEqual(harness.elements.submitbsearch.disabled, false);

  requests[0].response.resolve(htmlResponse('<p>Stale result</p>'));
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

  var exactLimitHtml = 'x'.repeat(256 * 1024);
  var exactLimitHarness = createHarness({
    fetch: function () {
      return Promise.resolve(htmlResponse(exactLimitHtml));
    }
  });
  exactLimitHarness.submit();
  await flushPromises();
  await flushPromises();
  assert.strictEqual(exactLimitHarness.elements.search_results.innerHTML.length, 256 * 1024, 'response exactly at limit should render');

  var oversizedHarness = createHarness({
    fetch: function () {
      return Promise.resolve(htmlResponse('x'.repeat((256 * 1024) + 1)));
    }
  });
  oversizedHarness.submit();
  await flushPromises();
  await flushPromises();
  assert.strictEqual(oversizedHarness.elements.search_results.innerHTML, '', 'oversized response should not render');
  assert.strictEqual(oversizedHarness.elements.search_results.textContent, 'Search is temporarily unavailable. Please try again.');

  var nonHtmlTextReads = 0;
  var nonHtmlHarness = createHarness({
    fetch: function () {
      return Promise.resolve({
        ok: true,
        headers: {get: function () { return 'application/json'; }},
        text: function () {
          nonHtmlTextReads += 1;
          return Promise.resolve('{}');
        }
      });
    }
  });
  nonHtmlHarness.submit();
  await flushPromises();
  assert.strictEqual(nonHtmlTextReads, 0, 'non-HTML response should be rejected before reading its body');
  assert.strictEqual(nonHtmlHarness.elements.search_results.textContent, 'Search is temporarily unavailable. Please try again.');

  var missingTypeTextReads = 0;
  var missingTypeHarness = createHarness({
    fetch: function () {
      return Promise.resolve({
        ok: true,
        headers: {get: function () { return null; }},
        text: function () {
          missingTypeTextReads += 1;
          return Promise.resolve('<p>Unexpected</p>');
        }
      });
    }
  });
  missingTypeHarness.submit();
  await flushPromises();
  assert.strictEqual(missingTypeTextReads, 0, 'missing content type should be rejected before reading its body');
  assert.strictEqual(missingTypeHarness.elements.search_results.textContent, 'Search is temporarily unavailable. Please try again.');

  var fallbackHarness = createHarness({fetch: undefined});
  assert.strictEqual(fallbackHarness.submit(), false, 'unsupported browsers should keep the native form submission');

  var missingAbortRequests = 0;
  var missingAbortHarness = createHarness({
    abortController: false,
    fetch: function () {
      missingAbortRequests += 1;
      return deferred().promise;
    }
  });
  assert.strictEqual(missingAbortHarness.submit(), false, 'missing AbortController should keep native submission');
  assert.strictEqual(missingAbortRequests, 0, 'missing AbortController should not start an asynchronous submit');
  assert.strictEqual(missingAbortHarness.elements.submitbsearch.disabled, false);

  var missingAbortPrefillRequests = 0;
  createHarness({
    abortController: false,
    company: 'OpenAI',
    fetch: function () {
      missingAbortPrefillRequests += 1;
      return deferred().promise;
    }
  });
  assert.strictEqual(missingAbortPrefillRequests, 0, 'missing AbortController should not start a prefilled search');

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
