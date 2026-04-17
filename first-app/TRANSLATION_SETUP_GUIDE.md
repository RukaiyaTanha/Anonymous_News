# Bangla Language & Translation Setup Guide

## ✅ What's Been Set Up

You now have a complete language preference and translation system!

### Core Features

#### 1. **User Language Settings** 
- Path: `/settings/language` (in Settings navigation menu)
- Users can choose: **English** or **Bangla (বাংলা)**
- Setting saved to `users.language` database field

#### 2. **Translation Service**
- Uses **Gemini AI API** (already configured)
- Translates text to Bangla naturally
- Available as service for reuse anywhere

#### 3. **Translation API Endpoints**
```
POST /translate/text
POST /translate/report
```

---

## 🎯 How to Use in Your Project

### A) Check User's Language Preference

```php
// In any blade view or controller
@if (auth()->user()->language === 'bn')
    <!-- Show Bangla content -->
@else
    <!-- Show English content -->
@endif
```

### B) Call Translation API (JavaScript)

**Translate single text:**
```javascript
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function translateText(englishText) {
    const response = await fetch('/translate/text', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ text: englishText })
    });
    
    const data = await response.json();
    return data.data.translated; // Bangla text
}

// Usage
const banglaText = await translateText("Hello World");
console.log(banglaText); // "হ্যালো ওয়ার্ল্ড"
```

**Translate Report (title + content):**
```javascript
async function translateReport(title, content) {
    const response = await fetch('/translate/report', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ title, content })
    });
    
    const data = await response.json();
    return {
        title_bn: data.data.title_bn,
        content_bn: data.data.content_bn
    };
}

// Usage
const result = await translateReport(
    "Breaking News",
    "Important information about..."
);
console.log(result.title_bn);   // Bangla title
console.log(result.content_bn); // Bangla content
```

### C) Use the Helper Class (Easy Way)

Already created a helper class! See `resources/js/ReportTranslator.js`

```javascript
import { ReportTranslator } from '@/ReportTranslator.js';

const translator = new ReportTranslator(
    document.querySelector('meta[name="csrf-token"]').content
);

// Translate
const banglaContent = await translator.translateText("Your English text");

// Or translate full report
const translations = await translator.translateReport(title, content);
```

---

## 📁 Files Created/Modified

### **New Files Created:**
1. `app/Services/TranslationService.php` - Translation logic
2. `app/Http/Controllers/TranslationController.php` - API endpoints
3. `resources/views/pages/settings/language.blade.php` - Language settings UI
4. `database/migrations/2026_02_25_add_language_to_users.php` - Add language field
5. `resources/js/ReportTranslator.js` - JavaScript helper class

### **Modified Files:**
1. `app/Models/User.php` - Added 'language' to fillable
2. `routes/settings.php` - Added language route
3. `routes/web.php` - Added translation routes
4. `resources/views/pages/settings/layout.blade.php` - Added language nav link
5. `config/services.php` - (already had Gemini config)
6. `.env` - (already has GEMINI_API_KEY)

---

## 🚀 Example: Add Translate Button to Report Form

### HTML (in reports/create.blade.php):
```html
<button type="button" id="translateReportBtn" class="btn btn-secondary">
    Translate to Bangla
</button>

<div id="translationPanel" style="display:none; margin-top:20px; padding:15px; border:1px solid #ddd; border-radius:8px;">
    <h4>Bangla Version:</h4>
    <div>
        <strong>Title:</strong>
        <p id="banglaTitle"></p>
    </div>
    <div>
        <strong>Content:</strong>
        <p id="banglaContent"></p>
    </div>
    <button type="button" id="useBanglaBtn" class="btn btn-primary">Use Bangla Version</button>
</div>
```

### JavaScript:
```javascript
import { ReportTranslator } from '@/ReportTranslator.js';

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const translator = new ReportTranslator(csrfToken);

document.getElementById('translateReportBtn').addEventListener('click', async () => {
    const title = document.getElementById('title').value;
    const content = document.getElementById('content').value;
    
    if (!title || !content) {
        alert('Please fill in title and content first');
        return;
    }
    
    try {
        document.getElementById('translateReportBtn').disabled = true;
        document.getElementById('translateReportBtn').textContent = 'Translating...';
        
        const result = await translator.translateReport(title, content);
        
        document.getElementById('banglaTitle').textContent = result.title_bn;
        document.getElementById('banglaContent').textContent = result.content_bn;
        document.getElementById('translationPanel').style.display = 'block';
        
        // Optional: Use Bangla version
        document.getElementById('useBanglaBtn').addEventListener('click', () => {
            document.getElementById('title').value = result.title_bn;
            document.getElementById('content').value = result.content_bn;
            document.getElementById('translationPanel').style.display = 'none';
        });
    } catch (error) {
        alert('Translation failed: ' + error.message);
    } finally {
        document.getElementById('translateReportBtn').disabled = false;
        document.getElementById('translateReportBtn').textContent = 'Translate to Bangla';
    }
});
```

---

## 🔧 Configuration

### Gemini API Key
Already configured in `.env`:
```
GEMINI_API_KEY=AIzaSyD5xrRZZKqrogdayKP5rmG2ND4-RrUsNTI
GEMINI_MODEL=gemini-2.5-flash-lite
```

### Settings File
Located at: `config/services.php`
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),
    'verify_ssl' => env('GEMINI_VERIFY_SSL', PHP_OS_FAMILY !== 'Windows'),
],
```

---

## ⚙️ Database Changes

### Users Table
Added column: `language` (string, default: 'en')

Options:
- `'en'` = English
- `'bn'` = Bangla

---

## 🎨 Next Steps (Optional Enhancements)

### 1. **Auto-Translate Reports**
Add bilingual storage:
```php
Schema::table('reports', function (Blueprint $table) {
    $table->string('title_bn')->nullable();
    $table->longText('content_bn')->nullable();
});
```

### 2. **Display Both Languages**
Show side-by-side English and Bangla in report view

### 3. **Language Switcher in Reports**
Let readers choose language when viewing reports

### 4. **Translation History**
Store translations for frequently accessed content to reduce API calls

---

## 📝 Testing

### Test in Browser:
1. Go to `/settings/language`
2. Select "Bangla (বাংলা)"
3. Click "Save Language Preference"
4. Should see success message
5. Check database: `users.language` should be `'bn'`

### Test Translation API:
```bash
# In terminal/Postman
curl -X POST http://localhost/translate/text \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -d '{"text":"Hello World"}'
```

---

## ❓ Troubleshooting

### Translation not working?
1. Check `.env` has `GEMINI_API_KEY`
2. Check Network tab in browser for 500 errors
3. Check Laravel logs: `storage/logs/laravel.log`

### Settings page not loading?
1. Clear cache: `php artisan cache:clear`
2. Clear config: `php artisan config:cache`

### Database error after migration?
```bash
# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

---

## 🎯 Summary

✅ Users can select language preference  
✅ Translation API ready to use  
✅ Gemini AI integration working  
✅ Helper JavaScript class available  
✅ Routes and database configured  

**You're ready to use bilingual features!** 🎉
