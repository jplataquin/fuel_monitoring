# Agent Guidelines

## Testing & Development
- Every time there are changes in CSS, HTML, or UI in general, run `npm run build`.
- To run the full test suite: `composer test`.
- **CRITICAL WARNING**: When running tests manually (e.g. `php artisan test`), **MUST ALWAYS** run `php artisan config:clear` beforehand to prevent falling back to the MySQL database and wiping it.

## Git Workflow & Shipping to Main
When instructed to push changes to remote `origin`:
In non-interactive CLI agent environments where terminal prompts are disabled, GUI credential managers may hang or fail due to command-line overrides. Always explicitly pass `-c credential.helper=store` to authenticate using stored credentials:
```bash
git -c credential.helper=store push origin main
```
