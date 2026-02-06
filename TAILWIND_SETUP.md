# Tailwind CSS Setup Guide

## Current Status

✅ **Tailwind CSS v4 is already installed and configured in this project!**

## Installation Details

### Installed Packages

- `tailwindcss: ^4.0.7` - Tailwind CSS v4
- `@tailwindcss/vite: ^4.1.11` - Tailwind Vite plugin
- `autoprefixer: ^10.4.20` - CSS autoprefixer

### Configuration Files

1. **`vite.config.js`** - Tailwind Vite plugin configured
2. **`resources/css/app.css`** - Tailwind imported and configured
3. **`package.json`** - Dependencies listed

## Setup Verification

### 1. Install Dependencies (if needed)

If you haven't installed dependencies yet:

```bash
npm install
```

### 2. Build Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 3. Verify Tailwind is Working

1. Start your Laravel server: `php artisan serve`
2. Visit your application
3. Check if Tailwind classes are being applied
4. Open browser DevTools → Network tab
5. Look for `app.css` file being loaded

## Tailwind CSS v4 Features

This project uses **Tailwind CSS v4**, which has a different configuration approach than v3:

### Key Differences

- **CSS-based configuration** instead of `tailwind.config.js`
- **`@source` directives** in CSS file for content paths
- **`@theme` directive** for custom theme configuration
- **Vite plugin** instead of PostCSS plugin

### Current Configuration

The Tailwind configuration is in `resources/css/app.css`:

```css
@import 'tailwindcss';

@source '../views';
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../vendor/livewire/flux-pro/stubs/**/*.blade.php';
@source '../../vendor/livewire/flux/stubs/**/*.blade.php';

@theme {
    /* Custom theme variables */
}
```

## Customization

### Adding Custom Colors

Edit `resources/css/app.css` and add to `@theme` block:

```css
@theme {
    --color-brand-500: #3b82f6;
    --color-brand-600: #2563eb;
}
```

### Adding Content Paths

Add `@source` directives:

```css
@source '../views/**/*.blade.php';
@source '../js/**/*.js';
```

### Custom Utilities

Add custom utilities in `@layer`:

```css
@layer utilities {
    .text-balance {
        text-wrap: balance;
    }
}
```

## Development Workflow

### Watch Mode (Development)

```bash
npm run dev
```

This will:
- Watch for file changes
- Rebuild CSS automatically
- Hot reload in browser

### Production Build

```bash
npm run build
```

This will:
- Optimize CSS
- Minify output
- Generate production-ready assets

## Troubleshooting

### Tailwind Classes Not Working

1. **Check if assets are built:**
   ```bash
   npm run build
   ```

2. **Clear Laravel cache:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Check Vite is running:**
   ```bash
   npm run dev
   ```

4. **Verify `@vite` directive in layout:**
   Check that your main layout includes:
   ```blade
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   ```

### Classes Not Being Detected

1. **Check `@source` directives** in `resources/css/app.css`
2. **Add your file paths** to `@source` if needed
3. **Rebuild assets:**
   ```bash
   npm run build
   ```

### Vite Plugin Issues

If you see Vite errors:

1. **Clear Vite cache:**
   ```bash
   rm -rf node_modules/.vite
   ```

2. **Reinstall dependencies:**
   ```bash
   rm -rf node_modules
   npm install
   ```

## Using Tailwind in Your Views

### Basic Usage

```blade
<div class="flex items-center justify-center p-4 bg-blue-500 text-white">
    Hello Tailwind!
</div>
```

### Responsive Design

```blade
<div class="text-sm md:text-base lg:text-lg">
    Responsive text
</div>
```

### Dark Mode

```blade
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
    Dark mode support
</div>
```

## Additional Resources

- [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)
- [Tailwind CSS v4 Migration Guide](https://tailwindcss.com/docs/upgrade-guide)
- [Vite Documentation](https://vitejs.dev/)

## Quick Commands Reference

```bash
# Install dependencies
npm install

# Development (watch mode)
npm run dev

# Production build
npm run build

# Check installed packages
npm list tailwindcss

# Clear Vite cache
rm -rf node_modules/.vite
```

---

**Your Tailwind CSS setup is complete and ready to use!** 🎉

