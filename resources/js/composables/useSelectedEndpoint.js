const METHOD_PARAM = 'method'
const URI_PARAM = 'uri'

export function findRouteFromUrl(routes) {
  const params = new URLSearchParams(window.location.search)
  const method = params.get(METHOD_PARAM)
  const uri = params.get(URI_PARAM)

  if (!method || !uri) {
    return null
  }

  return routes.find(
    (route) => route.http_method === method && route.uri === uri
  ) ?? null
}

export function syncSelectedRouteToUrl(route) {
  const url = new URL(window.location.href)
  url.searchParams.set(METHOD_PARAM, route.http_method)
  url.searchParams.set(URI_PARAM, route.uri)
  window.history.replaceState(window.history.state, '', url)
}

export function clearSelectedRouteFromUrl() {
  const url = new URL(window.location.href)

  if (!url.searchParams.has(METHOD_PARAM) && !url.searchParams.has(URI_PARAM)) {
    return
  }

  url.searchParams.delete(METHOD_PARAM)
  url.searchParams.delete(URI_PARAM)
  window.history.replaceState(window.history.state, '', url)
}
