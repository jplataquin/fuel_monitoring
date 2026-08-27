---
name: Fuel Monitoring Management System
description: A clean, high-contrast, task-oriented dark mode visual language designed for fleet utilization and fuel management.
colors:
  primary: "#0d6efd"
  neutral-bg: "#212529"
  neutral-fg: "#f8f9fa"
  surface: "#1c1b1f"
  surface-card: "#2c3034"
  scrollbar-track: "#1C1B1F"
  scrollbar-thumb: "#49454F"
  scrollbar-thumb-hover: "#CAC4D0"
  disabled-bg: "rgba(255, 255, 255, 0.03)"
  disabled-border: "rgba(255, 255, 255, 0.08)"
  disabled-text: "rgba(255, 255, 255, 0.3)"
typography:
  display:
    fontFamily: "Figtree, system-ui, -apple-system, sans-serif"
    fontSize: "2.5rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-0.02em"
  body:
    fontFamily: "Figtree, system-ui, -apple-system, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Figtree, system-ui, -apple-system, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 500
    lineHeight: 1.2
    letterSpacing: "normal"
rounded:
  sm: "4px"
  md: "8px"
  lg: "12px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.neutral-fg}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  button-secondary:
    backgroundColor: "{colors.scrollbar-thumb}"
    textColor: "{colors.neutral-fg}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
---

# Design System: Fuel Monitoring Management System

## Overview

**Creative North Star: "The Precise Depot Console"**

The Fuel Monitoring Management System's visual identity is inspired by highly functional, high-contrast physical hardware terminals and industrial dashboards. It features a dark-by-default environment optimized to minimize eye strain during long-shift operational use—especially for fuel-men logging utilization data and managers reviewing budgets on modern mobile and desktop screens.

**Key Characteristics:**
- **Task-Focused Clarity:** Layouts prioritize tables, rapid input form controls, and prominent status badges, reducing cognitive load during high-frequency data entry.
- **High-Contrast Dark Aesthetic:** Built on top of Bootstrap 5.3's dark theme using deeply dark, high-contrast surface elements and precise primary colors.
- **Micro-tactile feedback:** Clickable UI elements utilize standard transitions to feel modern, responsive, and tactile.

## Colors

The system uses a focused color palette ensuring high accessibility, consistent contrast, and strict adherence to dark-theme ergonomics.

### Primary
- **Command Blue** (#0d6efd): The primary brand color. Used for high-priority call-to-actions, primary navigation links, active states, and system-wide progress bars.

### Neutral
- **Deep Void Background** (#212529): Standard dark background for the core application canvas.
- **Industrial Track** (#1c1b1f): Used as the deep surface color for widgets, cards, and scrollbar tracks.
- **High-Contrast White** (#f8f9fa): Standard text color, guaranteeing maximum legibility against dark backgrounds.
- **Muted Steel** (#49454F): Used for secondary outlines, dividers, inactive states, and scrollbar thumbs.
- **Hover Metal** (#CAC4D0): High-intensity neutral used to draw focus to interactive thumbs or hovered dividers.

### Named Rules
**The Strict Dark Theme Rule.** The interface is built exclusively around the Bootstrap 5.3 dark theme (`data-bs-theme="dark"`). Light backgrounds are strictly forbidden except within the `@media print` layout, where the theme is automatically inverted to clean white for high-density paper outputs.

**The Interactive Highlight Rule.** Accent colors (Command Blue) must occupy less than 15% of any screen surface. Colors represent interactivity or status updates, not decoration.

## Typography

**Display Font:** Figtree, system-ui, sans-serif
**Body Font:** Figtree, system-ui, sans-serif

### Hierarchy
- **Display** (600, 2.5rem, 1.2): Used for hero headers, welcome titles, and primary analytical metrics.
- **Headline** (600, 1.75rem, 1.3): Used for page-level section titles and modal headers.
- **Title** (500, 1.25rem, 1.4): Used for card titles, table headers, and form-section labels.
- **Body** (400, 1rem, 1.5): Standard prose text, data rows, and description blocks.
- **Label** (500, 0.85rem, 1.2): Small detail metadata, badge texts, and form input labels.

### Named Rules
**The Condensed Heading Rule.** All headings (`h1` through `h6`) must carry a condensed letter spacing (-0.02em) to enforce a tight, technical, and authoritative layout structure.

## Layout

Layouts are designed to structure tabular data, dense forms, and system reports cleanly.

- **Responsive Grid:** Follows the Bootstrap standard 12-column flexbox grid.
- **Rhythm & Spacing:** Spatial hierarchy relies on 8px-based grid steps (xs: 4px, sm: 8px, md: 16px, lg: 24px, xl: 32px).
- **Ramp Density:** Standard components use spacious default sizing, shifting to highly compact, high-density layouts (`font-size: 0.75rem`, `line-height: 1.25`, tight padding) during printing or compact dashboard exports to pack critical information.

## Elevation & Depth

To remain grounded and maintain a clean terminal-like look, the design relies on flat tonal layering instead of aggressive drop shadows.

- **Depth Scale:** Zero shadow at rest. Interactive surfaces (cards, buttons) remain flush with the background, distinguished exclusively by subtle borders or slightly lighter container backgrounds (#2c3034).
- **Interactive Elevation:** State changes are represented by color-shifts and minor scaling transformations, rather than layered shadows.

### Named Rules
**The Flat Canvas Rule.** Drop shadows (`box-shadow`) are only permitted for floating components that exist outside the flat flow (e.g., modals, tooltips, or absolute navigation headers). Standard dashboards remain completely flat.

## Shapes

Shapes are geometric and highly uniform to present a precise and robust industrial feeling.

- **Corner Radius:** Elements use precise corners: small radius (4px) for input controls and small buttons, medium radius (8px) for cards, and large radius (12px) for custom modals or banners.
- **Borders:** Consistent, low-contrast subtle borders (rgba(255, 255, 255, 0.08)) define structural lines without polluting the visual canvas.

## Components

### Buttons
- **Shape:** Soft, tight rounded corners (4px).
- **Cursor State:** Always a hand pointer (`cursor: pointer`) on hover.
- **Hover Transitions:** Smooth state transitions over 200ms using the cubic-bezier timing function.

### Inputs / Fields
- **Default State:** Background (#212529) with a thin border. Dark color scheme is enforced on date and time pickers.
- **Disabled State:** Disabled fields use a dark translucent fill (rgba(255, 255, 255, 0.03)), high-transparency text (rgba(255, 255, 255, 0.3)), and a blocked cursor (`cursor: not-allowed`).

### Scrollbars
- **Webkit Scrollbars:** Custom scrollbars ensure scrollable areas look native to the dark theme.
- **Track Color:** Industrial Track (#1C1B1F).
- **Thumb Color:** Muted Steel (#49454F), transitioning to Hover Metal (#CAC4D0) when hovered.

## Do's and Don'ts

### Do:
- **Do** ensure all clickable anchor tags, select boxes, buttons, and custom button roles have `cursor: pointer` on hover.
- **Do** clear the Laravel configuration cache (`php artisan config:clear`) before running automated tests to prevent database wipes.
- **Do** use native Bootstrap spacing classes (`p-2`, `mb-3`, etc.) to preserve the 8px grid alignment.

### Don't:
- **Don't** use arbitrary inline hex color values for texts or backgrounds; instead, leverage Bootstrap's theme utility classes or standard system colors.
- **Don't** add complex drop shadows to standard widgets or dashboard cards.
- **Don't** disable smooth transition animations (`200ms cubic-bezier(0.4, 0, 0.2, 1)`) on interactive states.
