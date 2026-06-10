(function () {
  'use strict';

  var form = document.getElementById('searchform');
  var results = document.getElementById('search_results');
  var company = document.getElementById('search_term');
  var city = document.getElementById('city');
  var submit = document.getElementById('submitbsearch');
  var activeRequest = null;
  var latestRequest = 0;

  if (!form || !results || !company || !city || !submit) {
    return;
  }

  function supportsAsyncSearch() {
    return typeof window.fetch === 'function' && typeof window.URLSearchParams === 'function';
  }

  function requestSearch() {
    var requestNumber = latestRequest + 1;
    latestRequest = requestNumber;

    if (activeRequest) {
      activeRequest.abort();
    }

    var requestController = typeof window.AbortController === 'function' ? new window.AbortController() : null;
    var requestTimedOut = false;
    var requestTimeout = null;
    activeRequest = requestController;
    var body = new window.URLSearchParams();
    body.append('search_term', company.value.slice(0, 100));
    body.append('city', city.value.slice(0, 100));

    results.textContent = 'Searching salary data…';
    submit.disabled = true;

    var options = {
      method: 'POST',
      body: body,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    };
    if (requestController) {
      options.signal = requestController.signal;
      requestTimeout = window.setTimeout(function () {
        requestTimedOut = true;
        requestController.abort();
      }, 10000);
    }

    window.fetch(form.action, options)
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Search request failed');
        }
        return response.text();
      })
      .then(function (html) {
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

  if (supportsAsyncSearch() && company.value.trim() !== '') {
    requestSearch();
  }
}());
