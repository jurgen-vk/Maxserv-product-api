import path from 'node:path'
import { isCSSRequest } from 'vite'

export function contextAliases(root, aliases) {
  return {
    name: 'context-aliases',
    enforce: 'pre',
    async resolveId(source, importer, options) {
      const alias = Object.keys(aliases).find((a) => source.startsWith(`${a}/`))
      if (!alias) return null

      const target = importer && isCSSRequest(importer) ? aliases[alias].css : aliases[alias].js
      const resolved = path.resolve(root, target, source.slice(alias.length + 1))

      return this.resolve(resolved, importer, { ...options, skipSelf: true })
    },
  }
}