import { globSync } from 'tinyglobby'
import fs from 'node:fs'
import path from 'node:path'
import { normalizePath } from 'vite'

/**
 * Regenerates `assets/css/_components.generated.css` — a flat list of literal `@import`
 * statements for every colocated component `.css` file — before Vite/Tailwind ever see it.
 * `@import` itself doesn't support glob patterns, and both Vite's own import handling and
 * Tailwind's built-in import bundler only resolve literal paths, so the glob has to be
 * expanded to literal imports ourselves, ahead of time, rather than authored directly in CSS.
 */
export function cssComponentGlob(root) {
  const generatedFile = path.resolve(root, 'assets/css/_components.generated.css')

  const generate = () => {
    const files = globSync('templates/**/*.css', { cwd: root, absolute: true })
    const imports = files
      .map((file) => {
        const relativePath = normalizePath(path.relative(path.dirname(generatedFile), file))
        return `@import "${relativePath}";`
      })
      .join('\n')

    fs.writeFileSync(generatedFile, imports + '\n')
  }

  return {
    name: 'css-component-glob',
    buildStart: generate,
    configureServer(server) {
      generate()
      server.watcher.on('add', (file) => file.endsWith('.css') && generate())
      server.watcher.on('unlink', (file) => file.endsWith('.css') && generate())
    },
  }
}
