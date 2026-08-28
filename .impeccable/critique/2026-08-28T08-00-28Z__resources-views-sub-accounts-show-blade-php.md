---
target: resources/views/sub-accounts/show.blade.php
total_score: 23
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 2
timestamp: 2026-08-28T08-00-28Z
slug: resources-views-sub-accounts-show-blade-php
---
### Design Health Score

| # | Heuristic | Score | Key Issue / Improvement |
|---|-----------|-------|-------------------------|
| 1 | Visibility of System Status | 3 | Fast Alpine.js tab transitions. Submit buttons lack tactile processing indicators during network lag. |
| 2 | Match System / Real World | 4 | Excellent match. Clean field terms (\"Quantity\", \"Progress\") coupled with custom physical unit types. |
| 3 | User Control and Freedom | 2 | Tab navigation is free, but operators cannot edit accomplishments or budgets without re-creating. |
| 4 | Consistency & Standards | 1 | Design System drift: using `rounded-3` (12px) instead of `rounded-2` (8px) for cards and `rounded-1` (4px) for controls. |
| 5 | Error Prevention | 3 | Prevented future dates (`max`), but lacks warning checks on over-allocating remaining parent budgets. |
| 6 | Recognition Rather Than Recall | 2 | Top card pins metrics, but parent account limits are invisible when allocating budgets. |
| 7 | Flexibility and Efficiency | 2 | Quick single-item submission, but lacks keyboard focus hotkeys or multi-item bulk log actions. |
| 8 | Aesthetic and Minimalist Design | 3 | Pristine flat canvas with zero shadow. Cards feel slightly crowded on small desktop displays. |
| 9 | Error Recovery | 3 | Clear `.is-invalid` borders and red text render on validation failure. |
| 10 | Help and Documentation | 0 | No inline tooltips or helper documentation detailing operational limitations. |
| **Total** | | **23/40** | **Fair (Standard operational quality; core experience functional)** |

---

### Design Specificity Verdict

**LLM Assessment:**
The page demonstrates a strong transition toward "The Precise Depot Console" aesthetic by shifting from bloated layouts to flat tonal layers, crisp styling, and an efficient tabbed layout. However, it still exhibits **"Design System Drift"** where generic Bootstrap defaults clash with the strict guidelines of `DESIGN.md`.
- **Successes:** The flat canvas is well-respected with zero-shadow elements, Alpine.js tabbed navigation keeps related actions closely grouped without layout competition, and the monospace numeric data aligns perfectly with high-precision hardware terminals.
- **Failures:** The file violates key system rules, including the standard corner radius scale (using `rounded-3` instead of `rounded-2` or `rounded-1`), and the Interactive Highlight Rule (overusing `text-info` for static, read-only labels). The branding feels 80% industrial terminal and 20% default template.

**Deterministic Scan:**
The automated static analysis scan executed successfully and returned `3` advisory findings:
- **Line 41:** Inline style `font-size: 0.7rem;` is off the documented typography steps of `DESIGN.md`.
- **Line 58:** Inline style `font-size: 0.7rem;` is off the documented typography steps of `DESIGN.md`.
- **Line 242:** Inline style `font-size: 0.7rem;` is off the documented typography steps of `DESIGN.md`.

---

### Overall Impression
The overhauled sub-account details view is highly functional and structured. By introducing Alpine.js tab-switching and flat zero-shadow panel layers, we slashed the cognitive burden of navigating multiple expanded forms. However, standardizing utility classes (like corner radii) and fine-tuning label colors will lock in its alignment with the rugged terminal theme.

---

### What's Working
- **Lag-Free Form Tabs:** Replaced heavy page reloads and stacked action containers with client-side tabs, focusing the screen on a single task.
- **Excellent Outdoor Contrast:** The flat, solid borders and monospace typography make numerical progress highly legible for off-grid operations.
- **Accurate Date Safeguards:** Automatically locks date pickers to prevent logging future progress records.

---

### Priority Issues

#### **P1: Overuse of Accent Color on Static Labels (Strict Design System Violation)**
*   **Why it matters:** Standard static labels (e.g. \"Sub-Account Name\", \"Quantity\") are highlighted in cyan (`text-info`). This violates the Interactive Highlight Rule. Users assume these labels are clickable filter buttons or navigation links, causing visual friction.
*   **Concrete Fix:** Restore the static labels to secondary muted typography (`text-secondary`).
*   **Suggested command:** `$impeccable colorize` / `$impeccable polish`

#### **P1: Corner Radius & Input Scale Inconsistencies (Consistency & Standards)**
*   **Why it matters:** Cards use `rounded-3` (12px), violating the standardized shapes in `DESIGN.md` (medium 8px/`rounded-2` for cards, and small 4px/`rounded-1` for inputs). This breaks the uniform, robust terminal consoles aesthetic.
*   **Concrete Fix:** Apply `.rounded-2` to card wrappers and `.rounded-1` to form input controls.
*   **Suggested command:** `$impeccable layout` / `$impeccable quieter`

#### **P2: Lack of Visual Progress Bar (Cognitive Load)**
*   **Why it matters:** Raw numbers like `35.00%` require active parsing. In high-speed field operations, a horizontal graphical bar is essential for immediate, zero-effort visual reviews.
*   **Concrete Fix:** Add a thin, high-contrast, horizontal progress bar element beneath the progress percentage.
*   **Suggested command:** `$impeccable layout` / `$impeccable delight`

#### **P2: Blind Budget Allocations (Recognition Rather Than Recall)**
*   **Why it matters:** Budgeteers allocate sub-account budgets without knowing how much remaining balance is left on the parent chargeable account, leading to avoidable submit failures.
*   **Concrete Fix:** Display the remaining parent account balance capacity as description subtext inside the budget form.
*   **Suggested command:** `$impeccable clarify` / `$impeccable shape`

#### **P3: Literal CSS `font-size` Off-Ramp (Deterministic Findings)**
*   **Why it matters:** The inline font declarations `style="font-size: 0.7rem;"` bypass the standardized type scale steps documented in `DESIGN.md`, complicating global design system maintenance.
*   **Concrete Fix:** Replace inline styles with standard relative sizing utility classes (e.g., Bootstrap `.smaller` or `.small`).
*   **Suggested command:** `$impeccable typeset` / `$impeccable polish`

---

### Persona Red Flags

#### **Sam (Accessibility-Dependent)**
*   **Red Flag:** Active tabs rely exclusively on colored bottom borders (`border-success`, `border-info`). Color-blind users cannot easily distinguish which tab panel is active against deeply dark void canvases.
*   **Fix:** Add standard background opacity states (e.g., `bg-success bg-opacity-10`) to active tab links.

#### **Alex (Impatient Power User)**
*   **Red Flag:** Form submittal shifts cursor focus away from inputs, requiring operators logging progress records back-to-back to click the field every single time.
*   **Fix:** Retain focus or set autofocus to the quantity input following standard validation reloads.

#### **Jordan (Confused First-Timer)**
*   **Red Flag:** Confuses standard cyan label headers for navigation filters, clicking on static texts in vain.
*   **Fix:** Color headers strictly muted secondary colors to prevent dead-click actions.
