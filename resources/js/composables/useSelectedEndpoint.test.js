import { beforeEach, describe, expect, it } from 'vitest'
import {
  clearSelectedRouteFromUrl,
  findRouteFromUrl,
  syncSelectedRouteToUrl,
} from './useSelectedEndpoint.js'

const routes = [
  { http_method: 'GET', uri: 'api/users' },
  { http_method: 'POST', uri: 'api/users' },
  { http_method: 'GET', uri: 'api/users/{user}' },
]

const setLocation = (url) => {
  window.history.replaceState({}, '', url)
}

describe('findRouteFromUrl', () => {
  beforeEach(() => {
    setLocation('/api-docs')
  })

  it('returns null when query params are missing', () => {
    expect(findRouteFromUrl(routes)).toBeNull()
  })

  it('returns null when only the method param is present', () => {
    setLocation('/api-docs?method=GET')

    expect(findRouteFromUrl(routes)).toBeNull()
  })

  it('returns the matching route', () => {
    setLocation('/api-docs?method=GET&uri=api%2Fusers')

    expect(findRouteFromUrl(routes)).toEqual(routes[0])
  })

  it('returns null when no route matches', () => {
    setLocation('/api-docs?method=DELETE&uri=api%2Fusers')

    expect(findRouteFromUrl(routes)).toBeNull()
  })
})

describe('syncSelectedRouteToUrl', () => {
  beforeEach(() => {
    setLocation('/api-docs')
  })

  it('writes method and uri query params', () => {
    syncSelectedRouteToUrl(routes[0])

    expect(window.location.pathname).toBe('/api-docs')
    expect(window.location.search).toBe('?method=GET&uri=api%2Fusers')
  })

  it('preserves existing query params', () => {
    setLocation('/api-docs?foo=bar')

    syncSelectedRouteToUrl(routes[2])

    const params = new URLSearchParams(window.location.search)
    expect(params.get('foo')).toBe('bar')
    expect(params.get('method')).toBe('GET')
    expect(params.get('uri')).toBe('api/users/{user}')
  })
})

describe('clearSelectedRouteFromUrl', () => {
  it('removes endpoint query params', () => {
    setLocation('/api-docs?method=GET&uri=api%2Fusers&foo=bar')

    clearSelectedRouteFromUrl()

    const params = new URLSearchParams(window.location.search)
    expect(params.has('method')).toBe(false)
    expect(params.has('uri')).toBe(false)
    expect(params.get('foo')).toBe('bar')
  })

  it('does nothing when endpoint query params are absent', () => {
    setLocation('/api-docs?foo=bar')

    clearSelectedRouteFromUrl()

    expect(window.location.search).toBe('?foo=bar')
  })
})
