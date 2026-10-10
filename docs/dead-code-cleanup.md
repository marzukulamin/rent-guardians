# Dead-code cleanup — 10 October 2026

Removed confirmed unused application code after checking JavaScript scopes, imports and exports, runtime entry points, PHP function references, stylesheet selectors, tests and command-line tools.

- Removed 64 unused import bindings, including unused React defaults, icons, hooks and form helpers.
- Removed unused listing option helpers, the superseded service-order handler, HomeIcon, unused local/context bindings, and the unused PHP serialize_user helper.
- Removed the unused DemoTour re-export and made six exports private where their implementations are still used within their own modules.
- Removed 50 obsolete CSS rules and 56 obsolete selectors across four stylesheets, including the former floating home note, ecosystem bars, admin tabs/banner, image caption and inline search.
- Removed public/fonts-source.css, an unreferenced duplicate font stylesheet. The active local font declarations and font files remain in use.
- Preserved asynchronous sign-in requests when removing unused response variables.

All frontend modules and package dependencies are reachable. Dynamic badge and service-art selectors are active. Positional callback placeholders remain necessary. Setup, migration, seeding, maintenance, deployment and QA commands are independent entry points. Ignored third-party design tooling, generated files and database storage were excluded from application-source deletion.

Verification: production build passed; all 27 regression tests passed; PHP bootstrap syntax passed; existing mobile browser QA passed 26 routes across eight roles with no horizontal overflow or browser errors. The final source audit found no further unused application bindings other than positional placeholders, no unreferenced PHP named functions, and no unconditional unreachable JavaScript statements. The request export is used by the regression tests.

The workspace has no Git repository. Before-edit copies are retained under storage/dead-code-backup for recovery.
