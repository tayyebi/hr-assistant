# Copilot Instructions

## Project Overview
Humap Capitals Management System (HCMS)

## Coding behaivour:

- **Update the related documents**: Update `README.md` and `TODO.md` when things chaned.

## Design principles

- **Zero JavaScript**: Pure server-side rendering with CSS-based interactivity - no client-side JavaScript code
- **Pure PHP Implementation**: Zero external dependencies - no Composer or third-party packages require
- **Mobile-first Stylesheets**: Design for mobile, use media queries for larger screens.
-- **No dependencies**: No third-parties.

## Test Driven Development

- **E2E Tests**: For each requirement, create smoke tests in bash script and only bash script. Even for unit tests, expose them somewhere and test with bash script. bash script is the single source of truth for tests.

## Code Hygine

- **Comments**: No comments between the lines. Keep all comments at the top. If there was an standard, such as ISO or RFC, mention that.
- **Atomic Files**: Single purpose.

## Chats

- **No fluf**: No talk, just code; except when planning.