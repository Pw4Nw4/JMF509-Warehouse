---
name: AyitiCo Modern Marketplace Redesign
overview: Transform the current Amazon-inspired JMF 509 Warehouse into a modern, unique Haitian marketplace called "AyitiCo" with a tech startup aesthetic, featuring dark mode styling, modern UI elements, and responsive design.
todos:
  - id: update_config
    content: Update config.php with new AyitiCo branding
    status: completed
  - id: redesign_css
    content: Redesign jmf509_styles.css with modern tech startup aesthetic
    status: completed
  - id: update_header
    content: Update header.php with new branding
    status: completed
  - id: update_footer
    content: Update footer.php with F09 tech attribution
    status: completed
  - id: redesign_landing
    content: Redesign index.php landing page with modern hero and layout
    status: completed
  - id: update_other_pages
    content: Update other page titles (items.php, cart.php)
    status: completed
isProject: false
---

## Plan: AyitiCo Modern Marketplace Redesign

### Files to Modify

1. **[config.php](config.php)** - Update branding
  - Change `APP_NAME` to "AyitiCo"
  - Change `BUSINESS_NAME` to "AyitiCo"
  - Change `SUPPORT_EMAIL` to "[support@ayitico.com](mailto:support@ayitico.com)" (placeholder)
  - Add new constant: `define('POWERED_BY', 'Product of F09 tech');`
2. **[jmf509_styles.css](jmf509_styles.css)** - Complete CSS overhaul
  - Replace color palette with modern tech startup theme:
    - Primary: `#0f172a` (dark navy/slate)
    - Accent: `#06b6d4` (cyan) + `#f97316` (warm orange for CTAs)
    - Background: `#ffffff` and `#f8fafc` (light mode base)
    - Cards: `#ffffff` with subtle shadows
  - Add new design elements:
    - Modern card hover effects with translateY and shadow
    - Gradient hero section with geometric pattern
    - Refined typography (keep Inter, add display font for headings)
    - Smooth transitions and micro-animations
    - Better spacing and visual hierarchy
  - Mobile-first responsive improvements
3. **[Extras/header.php](Extras/header.php)** - Update branding
  - Change search placeholder to "Search AyitiCo"
  - Update logo display to use BUSINESS_NAME
4. **[Extras/footer.php](Extras/footer.php)** - Add branding
  - Add "AyitiCo - Product of F09 tech" in footer-bottom
  - Update all links and text to reflect new name
5. **[index.php](index.php)** - Redesign landing page
  - Create compelling hero with background gradient/pattern
  - Add value proposition section with icons
  - Improve product grid presentation
  - Add "Why Choose AyitiCo" trust section
  - Better category showcase
6. **[items.php](items.php)** - Minor updates
  - Update page title to AyitiCo
7. **[cart.php](cart.php)** - Minor updates
  - Update page title to AyitiCo

### Design Approach (Avoiding Amazon Copy)

- **Unique color scheme**: Dark slate/cyan instead of dark blue/orange
- **Different layout**: More white space, asymmetric grids, card-based design
- **Modern typography**: Keep Inter for body, use for headings with varied weights
- **Distinctive hero**: Gradient with pattern instead of solid color
- **Trust signals**: Different placement and styling for trust badges
- **Footer**: Clean, centered branding with F09 tech attribution

