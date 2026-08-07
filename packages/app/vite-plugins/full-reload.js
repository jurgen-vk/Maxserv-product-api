import picomatch from 'picomatch'
import path from 'node:path'
import { normalizePath } from 'vite'

/**
 * Broadcasts a full-reload over the dev-server WebSocket when a file matching one of
 * `patterns` changes. For files outside Vite's module graph (e.g. server-rendered Twig
 * templates) there's no module to hot-patch, so a hard reload is the only option.
 */
export function fullReload(root, patterns) {
  const absolutePatterns = patterns.map((pattern) => normalizePath(path.resolve(root, pattern)))
  const isMatch = picomatch(absolutePatterns)

  return {
    name: 'full-reload',
    configureServer(server) {
      server.watcher.on('change', (file) => {
        if (isMatch(file)) server.ws.send({ type: 'full-reload', path: '*' })
      })
    },
  }
}