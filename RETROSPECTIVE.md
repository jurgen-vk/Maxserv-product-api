# Retrospective

## Table of Contents

- [Context](#context)
- [Mistakes I Made](#mistakes-i-made)
- [What I Built, and Why](#what-i-built-and-why)
- [Next Sprint](#next-sprint)

---

## Context

I went into this project not really knowing Symfony, Twig, or a lot of the other things I ended up using, this was all
pretty new to me, so I had to learn a lot along the way. I made plenty of mistakes that I only corrected once I had more
information or ran into problems firsthand. I'm normally used to working in Laravel, but building this has made me quite
comfortable in Symfony as well. I'm not an expert in a lot of what I used here, but I did my best to set everything up
professionally, and especially with scalability and developer-friendliness in mind, and I did a fair amount of research
along the way.

I care a lot about this, and I know MaxServ does too. My standards are pretty high, and since the assignment said I
could take the time I needed to make something I was proud of, and to show my capabilities, I took advantage of that. I
could have put something sloppy together in a day or two, but that wouldn't really show what I'm capable of or where my
strengths are.

I did take a fair amount of calendar time before sending this back, but I want to be upfront that I didn't actually work
on it for as long as that might suggest. I had to postpone the assignment and had very little time in between, since I
ended up taking over informal care for my grandpa after my mom was injured, and I also had to help her get back to work.
On top of that, I've been unusually busy with a number of other things that kept me occupied for a while. That's all
resolved now, so I'm glad to have finished it, and to have made something I'm proud of.

I made some mistakes along the way and had to refactor the codebase more than once, which slowed me down somewhat,
mostly because I wasn't yet familiar with this environment. I am now. Below, I want to walk through what I built, the
mistakes I made, and the reasoning behind the decisions I made. I've also kept a list of things I wanted to add but had
to draw the line somewhere, those are noted at the end as candidates for a "next sprint."

---

## Mistakes I Made

### 1. Using Tailwind

I don't like putting all my CSS into one huge file. I think that's bad practice and unfriendly to future developers, so
I initially opted for Tailwind, even though I wasn't especially familiar with it. I was using some fairly new Tailwind
features and got everything working fine. The problem was that I also like to scope my CSS per component and avoid
cluttering my HTML with long strings of utility classes, so I leaned heavily on `@apply`. That defeats a lot of the
point of Tailwind in the first place, and it turned into a cluttered mess. I ended up ripping it out in favor of plain,
structured, vanilla CSS files per component.

### 2. Using Alpine.js

I used Alpine for a while. In hindsight this probably wasn't a huge issue, but I didn't want to take the risk: Alpine
describes itself as a framework, and the assignment explicitly said no frameworks. Since Twig was already set up for me,
I figured frontend frameworks were off the table as well, so I removed it after actually looking up Alpine's own
definition of itself, admittedly a bit later than I should have. I didn't want to take the risk, so I replaced it with
jQuery instead, which also lets me write JavaScript efficiently.

### 3. Loose Version Control

I didn't make good use of version control on this project. My normal workflow is more methodical and structured, but I
jumped straight into building here and let good habits slip. As a result, some commits change large parts of the
codebase at once, and I often didn't describe what I'd actually done in the commit message. I do know how to do this
properly, I just fell out of the habit, and fixing the history after the fact would have taken more time without really
being honest about what happened. Writing this document is partly meant to make up for that gap.

### 4. "Mistake": Iterating Too Much

I probably spend a bit too much time iterating and improving things whenever I notice something that isn't quite right
or optimal. The more research I do, the more I find things I did "wrong," and I don't like leaving those things in, so I
fix them. That's happened often enough to slow me down here and there, especially since I had no prior experience with
most of what I was working with.

To be clear, by "wrong" I don't mean broken, I mean not quite optimal in a way that could cause trouble later; things
worked fine the vast majority of the time. I like thinking about system architecture and how to set things up in a
scalable, developer-friendly way. It costs a bit more time up front, but it saves a lot more further down the line. I'm
hoping that's something valued here rather than seen as a downside.

### 5. "Mistake": Possibly Over-Engineering, and Making Some Executive Calls Outside the Requirements

I implemented everything in the requirements, plus a few extras that felt like logical, nice-to-have additions (to be
clear, I wouldn't make unrequested changes like this for an actual client without asking first). I kept improving things
because I thought it would be valuable and would show what I'm capable of and how quickly I can pick up new skills. That
did add extra time to the assignment for things that weren't actually asked for, which probably wasn't ideal. I thought
it would be a good way to demonstrate my ability, but in hindsight I probably shouldn't have gone as far as I did; I was
mostly just enjoying the work and kept finding more things I wanted to add.

---

## What I Built, and Why

I started with a quick prototype, taking some time up front to think through how I'd fit the requirements together. As I
kept researching and finding better ways to write the code, I refactored the starter codebase I was given to better
match conventions I found for Symfony projects, and to set things up in a more scalable way. That's why things look
somewhat different from the starting point, I built on top of it rather than replacing it, and hopefully just organized
it better.

I decided against using an ORM, even though I would genuinely have liked to. Partly because I felt it would be more in
the spirit of the assignment to show I can work with SQL directly, via a plain repository pattern instead of a heavy
abstraction, and partly because an ORM felt like pulling in a large chunk of framework-sized functionality, which didn't
seem in line with the "no frameworks" rule.

After the initial prototype, I refactored again to address the mistakes above and to apply what I'd learned by that
point. I decided it made sense for imports to run asynchronously in the background instead of blocking the page, and,
alongside that, to stream progress notifications from the backend to the browser. That's what led me to Mercure and
Messenger, neither of which I'd used (or heard of) before. With some trial and error I got them set up in a way I'm
fairly proud of professionally. This felt like one of the most valuable improvements I could make to the app, and it
took a while to fully understand, both how the two pieces work together, and how to wire all of it up correctly in
Docker.

Alongside that, I added a page to view past imports (a nice-to-have), and extended the sorting/filtering capabilities
using modern web platform features.

Some smaller decisions:

- I upgraded the PHP version used by the app, since I like staying current and using newer language features.
- I set up a proper migrations system, along with several useful commands to go with it.
- I added a handful of additional console commands to streamline day-to-day development.
- I paid close attention to DRY and SOLID, decoupling things wherever it made sense and following established best
  practices; the Twig templates are structured to be reusable in the same way.
- I wrote a few custom Vite plugins to keep the project structured clearly and to streamline the build.
- I wrote a few small jQuery plugins to write cleaner, more scalable frontend code.
- I added a fair amount to `core`. I think everything in there genuinely belongs there: it's mostly wiring and genuinely
  global functionality. I also restructured it somewhat to be more scalable, more developer-friendly, and closer to
  established conventions.
- I ran into a good number of bugs along the way, all of which I believe I resolved properly, without workarounds or
  hacky fixes.

---

## Next Sprint

Things I deliberately left out, in rough priority order.

### High priority / benefit

- Swap the database from MySQL to PostgreSQL so the database can emit events that Messenger/Mercure can react to, or
  better yet, introduce Redis so Messenger/Mercure don't have to keep polling the database every second.
- Use an ORM instead of a plain repository pattern, e.g. Doctrine, Laravel's Eloquent, or something else; there are
  plenty of good options.
- Write tests for the application.
- Consider using FrameworkBundle, or migrating to the full Symfony framework instead of just its components (out of
  scope for this assignment).
- Set up a failure transport in Messenger.
- Use `symfony/scheduler` to run imports automatically on a schedule.
- Add validation, e.g. via `symfony/validator`.
- Handle imports far more robustly: better error handling, resuming a partially-crashed import instead of failing the
  whole thing, skipping individual broken products, and so on. Right now there's essentially no error handling: if a
  single product fails to import, the entire import fails and Messenger retries it up to three times, with no validation
  in between. This probably needs an intermediate caching layer such as Redis.
- The Messenger/import setup as a whole is fairly fragile: there's no intermediate layer that caches imported items
  before they're committed to the database, so an import can't actually be cancelled cleanly right now without leaving
  residual data behind. If something goes wrong on Messenger's side, there's also no way for an import to be marked as
  failed, it'll just stay "running" forever, and there's nothing in place to detect whether Messenger itself is alive,
  stuck, or has died. This is a significant gap and a high priority to address properly.
- Add a cancel button for running imports. This needs the same intermediate caching layer mentioned above (to avoid
  leaving residual data on cancel), and could also support resuming a failed import.
- The assignment doesn't require it, but the external API returns more data than is currently stored; worth expanding
  on.
- Have Mercure updates be dispatched through Messenger too (mainly worthwhile once Redis is in place).

### Medium priority / benefit

- Adopt a more established migrations tool, e.g. `doctrine/migrations`.
- Add proper, structured (error) logging throughout the app and its services.
- The current database access is fairly hardcoded throughout the app and not easily swappable later; worth revisiting.
- Add PHPStan, and write structured PHPDoc comments.
- If allowed, use a frontend framework such as Svelte or Vue, with TypeScript.
- Add a way to toggle which columns are shown on the products page.
- Switch pagination from offset-based to keyset-based.
- For the category/brand filters, fetch options on demand with debounced requests instead of loading all of them
  upfront.
- Add a fallback for popover positioning, since the API is still quite new.
- I'm relying on some fairly new browser and PHP features with no fallbacks; older browsers may not work correctly.
  Worth considering. I did check forehand, and everything I use should be available for a while. So every major browser
  updated to a somewhat new version shouldn't have any problems.
- Add infinite scroll as an alternative/addition to pagination.
- Consider exposing an API or GraphQL endpoint to swap data client-side instead of always re-rendering a server-side
  partial.
- Prefer container queries over media queries for some elements.

### Low priority / benefit

- Add a test that checks all routes are unique and don't clash.
- Handle per-environment configuration more cleanly.
- Replace `guzzlehttp/guzzle` with `symfony/http-client` once things get more complex, or if async-first behavior
  becomes a priority (not urgent for now).
- Having already upgraded PHP, I'd like to update the rest of the stack too, e.g. the Symfony version.
- Add generator commands for more things, e.g. scaffolding a controller, similar to how `make:template` works, with
  flags like `--resource`.
- I'd love a small set of view-rendering helper functions, mirroring Laravel's `view()`, instead of
  `new Response($this->templateRenderer->render(...))` everywhere. That said, this might go against the stricter
  dependency-injection conventions Symfony tends to favor; worth a discussion.
- Extend search in filters to support field-scoped queries, e.g. `id:{id}` or `title:{title}`.
- Could add a CDN, though that feels like overkill at this stage.
- Consider ARIA roles/labels on elements, though that may be semantic overkill for a project like this.
- Add action buttons to the imports table, e.g. to cancel a running import.
- Build a component renderer (or extend `core`'s fragment renderer) so UI components can be fetched and rendered on
  demand; would need a new controller and endpoint. Not necessary yet.
- Record milliseconds in the imports table's `started_at`/`ended_at` columns; don't currently see much benefit in it.
- Add another small jQuery plugin for `IntersectionObserver` and similar browser APIs.
