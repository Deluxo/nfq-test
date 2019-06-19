getSlug = function() {
  element = document.querySelector('[name$="[slug]"]');

  if (!(element instanceof HTMLInputElement)) {
    return 0;
  }

  return element.value;
};

applyRoute = function(routeKey, params) {
  if (!routes) {
    return '';
  }

  var route = routes[routeKey];

  for (var key in params) {
    route = route.replace('{' + key + '}', params[key]);
  }

  return route;
};

deleteButton = function(element) {
  var slug = getSlug();

  if (element.dataset.action && element.dataset.action in routes && slug) {
    element.setAttribute('data-path', applyRoute(element.dataset.action, {'slug': slug}));
    element.addEventListener('click', function() {
      window.location = element.dataset.path;
    });
  } else {
    element.setAttribute('disabled', 'true');
    element.classList.add('disabled');
  }
};

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-action]').forEach(deleteButton);
});
