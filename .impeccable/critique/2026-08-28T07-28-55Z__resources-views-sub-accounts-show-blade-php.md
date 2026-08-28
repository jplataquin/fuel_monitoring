---
target: resources/views/sub-accounts/show.blade.php
total_score: 16
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 1
timestamp: 2026-08-28T07-28-55Z
slug: resources-views-sub-accounts-show-blade-php
---
### Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 2 | Raw text percentage; no visual progress bar. No success flash/toasts on log. |
| 2 | Match System / Real World | 2 | Technical jargon (\"Accomplishment Registry\"); budget in Liters vs progress in physical units. |
| 3 | User Control and Freedom | 1 | No edit/undo on logged accomplishments; moderators cannot delete their entries. |
| 4 | Consistency & Standards | 1 | Giant rounded card corners (`rounded-4`) and custom borders break flat terminal guidelines. |
| 5 | Error Prevention | 2 | No date restrictions (future dates allowed); no allocation budget checks. |
| 6 | Recognition Rather Than Recall | 2 | Forces manual math across disparate columns to understand progress. |
| 7 | Flexibility and Efficiency | 2 | No hotkeys, autofocus states, or multi-item bulk log entries. |
| 8 | Aesthetic and Minimalist Design | 2 | Heavy visual outlines and bloated paddings (`py-5`, `p-4`) force excessive scrolling. |
| 9 | Error Recovery | 2 | Small red text is hard to see; input border does not toggle state (missing `.is-invalid`). |
| 10 | Help and Documentation | 0 | No inline micro-copy or helper tooltips explaining the workflow. |
| **Total** | | **16/40** | **Poor (Major UX overhaul required; core experience broken)** |

---

### Design Specificity Verdict

**LLM Assessment:**
While the page successfully handles database models specific to the product (like `SubAccountBudget` and `AccomplishmentRegistry`), the visual language and composition are category-interchangeable. The layout uses standard Bootstrap card grids, generic form elements, and stock visual constructs.
- **Unit Gaps:** The Approved Budget is hardcoded to Liters ("L"), but the Accomplishment Registry uses a dynamic `$subAccount->unit` (e.g., Sqm, hours, tons, etc.). The page tracks these in parallel but fails to expose the *fuel consumption factor* that links them. This makes the interface feel like a generic spreadsheet rather than a cohesive, domain-specific fleet fueling dashboard.
- **Formality over Field Reality:** Terms like "Accomplishment Registry" and "Accomplishment History" represent corporate jargon. In actual fleet/field operations (e.g., construction sites or depots), operators look for tactile logs like "Work Completed" or "Activity Log."

**Deterministic Scan:**
The automated static analysis scan executed flawlessly and returned `0` non-compliant syntax warnings.

**Visual Overlays:**
No live browser automation tool was active this session. A static fallback assessment was used, and console logging skipped cleanly.

---

### Overall Impression
The sub-account details page provides a solid functional baseline but suffers from high cognitive load and visual clutter. By striving for generic consumer styles (like bubbly pill buttons and heavy drop shadows), it misses the chance to feel like a high-density, authoritative, and precise industrial hardware terminal tailored for the depot floor.

---

### What's Working
- **Tabular Information Grid:** The top sub-account card neatly consolidates five key quantitative variables, using monospace fonts (`font-monospace`) to make numerical tracking easy to read.
- **Granular Role-Based Form Gating:** Forms are securely hidden from unauthorized users (e.g., only `administrator` and `budgeteer` can allocate budgets; non-admins cannot delete accomplishments).
- **Audit-Ready History Tables:** Chronological tables for both accomplishments and budgets are cleanly structured with visible dates, relative times, and status badges.

---

### Priority Issues

#### **P0: Invisible Validation Error Focus (Heuristic 9)**
*   **Why it matters:** Users entering data on mobile devices outdoors cannot easily spot small red text on dark backgrounds. If a form fails validation, the input itself does not turn red, making troubleshooting slow and frustrating.
*   **Concrete Fix:** Add the standard Bootstrap validation class conditionally using Laravel's `@error` directive.
*   **Suggested command:** `$impeccable harden` / `$impeccable clarify`

#### **P1: Aesthetic Divergence from the "Precise Depot Console" (Consistency & Standards)**
*   **Why it matters:** The design system (`DESIGN.md`) demands a flat-canvas terminal theme. The presence of `shadow-lg` elevation, rounded-pill shapes, and giant `rounded-4` borders makes the app look like a generic web template, diluting its professional, industrial brand promise.
*   **Concrete Fix:**
    1. Remove `shadow-lg` and custom `border-start border-4` styles. Keep cards completely flat using simple, low-contrast solid borders.
    2. Standardize corner radii: Replace `rounded-4` on cards with `rounded-3` (8px, matching `rounded.md`). 
    3. Replace bubbly `rounded-pill` buttons/badges with a sharper, more precise structure (`rounded-1` / 4px, matching `rounded.sm`).
*   **Suggested command:** `$impeccable layout` / `$impeccable quieter`

#### **P2: Workflow Noise via Dual Uncollapsed Forms (Cognitive Load)**
*   **Why it matters:** Presenting both operations (Accomplishments and Budgets) fully expanded side-by-side on a single page is highly intimidating and causes cognitive fatigue.
*   **Concrete Fix:** Implement a clean, flat-styled tab component (using Bootstrap `.nav-tabs` or `.nav-pills` with a terminal border aesthetic) to toggle between **"Progress Log"** and **"Fuel Budget Allocation."** This keeps the screen highly focused.
*   **Suggested command:** `$impeccable distill` / `$impeccable shape`

#### **P3: CSS Typography Typo `uppercase` (Consistency & Standards)**
*   **Why it matters:** The approved budget card metric uses `<span class=\"h6 text-secondary uppercase\">L</span>`. Because `uppercase` is not a valid Bootstrap class, the character "L" may render in lowercase on various browsers, violating standard unit guidelines.
*   **Concrete Fix:** Change `uppercase` to the correct Bootstrap utility `text-uppercase`.
*   **Suggested command:** `$impeccable typeset` / `$impeccable polish`

---

### Persona Red Flags

#### **Sam (Accessibility-Dependent)**
*   **Red Flag:** The breadcrumbs and inline text links use `.text-info` which has poor color contrast against the dark background. Under low-light or outdoor glare, these links become completely illegible.
*   **Red Flag:** The delete button icon is standard red (`text-danger`) but has no high-contrast hover state, explicit focus ring, or screen-reader descriptor (`aria-label="Delete accomplishment entry"`).

#### **Alex (Impatient Power User)**
*   **Red Flag:** The bloated layout paddings (`py-5`, `p-4`) force excessive vertical scrolling on laptop and tablet screens.
*   **Red Flag:** The inputs lack autofocus on page load. An operational supervisor wanting to log a quick log has to manually click the input field every single time.

#### **Jordan (Confused First-Timer)**
*   **Red Flag:** No contextual explanation of the core workflow. Jordan will not understand why registering a physical accomplishment (e.g., cubic meters paved) is presented on the same page as a budget allocated in Liters.
*   **Red Flag:** There is no empty state description. If no history is found, the screen displays a generic "No accomplishment history found" without guidance on how to create the first log.

---

### Minor Observations
- **Arbitrary Inline Styling:** The use of `style="max-width: 1000px;"` and `style="font-size: 0.75rem;"` bypasses the SCSS and Tailwind-alternative utilities, violating codebase maintainability standards.
- **Lack of Hover Pointer Cursor:** Standard buttons use `btn` which gets a pointer cursor on hover, but the custom svg buttons (`btn-link`) lack explicit focus/pointer cursor rules on some screen types, breaking the **UI Design Standards** in `GEMINI.md`.
- **Legacy Confirmation Dialog:** Relying on native browser `onclick="return confirm(...)"` feels outdated, jarring, and poorly styled compared to the dark theme console aesthetic.

---

### Questions to Consider
1. *“If our design guidelines mandate 'The Precise Depot Console'—inspired by rugged, physical terminal hardware—why are we styling actions like bubbly consumer social-media buttons (`rounded-pill`, `shadow-lg`) instead of leveraging flat, high-density, geometric controls?”*
2. *“Are Accomplishments and Fuel Budgets actually disconnected? If 100 Sqm of construction consumes an estimated 250 Liters of fuel, why doesn't the interface explicitly show this mathematical consumption factor on the screen to help the operator understand the relationship between physical progress and fuel replenishment?”*
