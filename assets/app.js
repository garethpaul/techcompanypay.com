(function () {
  'use strict';

  var form = document.getElementById('searchform');
  var results = document.getElementById('search_results');
  var company = document.getElementById('search_term');
  var city = document.getElementById('city');
  var submit = document.getElementById('submitbsearch');
  var activeRequest = null;
  var latestRequest = 0;
  var MAX_SEARCH_RESPONSE_LENGTH = 256 * 1024;

  if (!form || !results || !company || !city || !submit) {
    return;
  }

  function supportsAsyncSearch() {
    return typeof window.fetch === 'function' &&
      typeof window.URLSearchParams === 'function' &&
      typeof window.AbortController === 'function' &&
      typeof window.Blob === 'function';
  }

  function responseContentType(response) {
    if (!response.headers || typeof response.headers.get !== 'function') {
      return '';
    }
    return (response.headers.get('Content-Type') || '').split(';', 1)[0].trim().toLowerCase();
  }

  function responseByteLength(html) {
    return new window.Blob([html]).size;
  }

  function declaredResponseByteLength(response) {
    if (!response.headers || typeof response.headers.get !== 'function') {
      return null;
    }
    var value = response.headers.get('Content-Length');
    if (typeof value !== 'string' || !/^(0|[1-9][0-9]*)$/.test(value)) {
      return null;
    }
    return Number(value);
  }

  function boundedInputValue(value) {
    var result = '';
    var offset = 0;
    var characters = 0;
    while (offset < value.length && characters < 100) {
      var first = value.charCodeAt(offset);
      var width = 1;
      if (first >= 0xD800 && first <= 0xDBFF) {
        var second = value.charCodeAt(offset + 1);
        if (!(second >= 0xDC00 && second <= 0xDFFF)) {
          return '';
        }
        width = 2;
      } else if (first >= 0xDC00 && first <= 0xDFFF) {
        return '';
      }
      result += value.slice(offset, offset + width);
      offset += width;
      characters += 1;
    }
    return result;
  }

  function requestSearch() {
    var requestNumber = latestRequest + 1;
    latestRequest = requestNumber;

    if (activeRequest) {
      activeRequest.abort();
    }

    var requestController = new window.AbortController();
    var requestTimedOut = false;
    activeRequest = requestController;
    var body = new window.URLSearchParams();
    body.append('search_term', boundedInputValue(company.value));
    body.append('city', boundedInputValue(city.value));

    results.textContent = 'Searching salary data…';
    submit.disabled = true;
    results.setAttribute('aria-busy', 'true');

    var options = {
      method: 'POST',
      body: body,
      headers: {'X-Requested-With': 'XMLHttpRequest'},
      signal: requestController.signal
    };
    var requestTimeout = window.setTimeout(function () {
      requestTimedOut = true;
      requestController.abort();
    }, 10000);

    window.fetch(form.action, options)
      .then(function (response) {
        if (!response.ok || responseContentType(response) !== 'text/html') {
          throw new Error('Search request failed');
        }
        var declaredLength = declaredResponseByteLength(response);
        if (declaredLength !== null && declaredLength > MAX_SEARCH_RESPONSE_LENGTH) {
          throw new Error('Search response is too large');
        }
        return response.text();
      })
      .then(function (html) {
        if (responseByteLength(html) > MAX_SEARCH_RESPONSE_LENGTH) {
          throw new Error('Search response is too large');
        }
        if (requestNumber === latestRequest) {
          results.innerHTML = html;
        }
      })
      .catch(function (error) {
        if ((error.name !== 'AbortError' || requestTimedOut) && requestNumber === latestRequest) {
          results.textContent = 'Search is temporarily unavailable. Please try again.';
        }
      })
      .then(function () {
        if (requestTimeout) {
          window.clearTimeout(requestTimeout);
        }
        if (requestNumber === latestRequest) {
          activeRequest = null;
          submit.disabled = false;
          results.removeAttribute('aria-busy');
        }
      });
  }

  form.addEventListener('submit', function (event) {
    if (!supportsAsyncSearch()) {
      return;
    }
    event.preventDefault();
    requestSearch();
  });

  if (supportsAsyncSearch() && (company.value.trim() !== '' || city.value.trim() !== '')) {
    requestSearch();
  }
}());
