/**
 * Translation Helper for Report Submissions
 * 
 * Add this to your Vite JavaScript pipeline (resources/js/)
 * Then import it in your app.js
 */

export class ReportTranslator {
    constructor($csrfToken) {
        this.$csrfToken = $csrfToken;
        this.isTranslating = false;
    }

    /**
     * Translate both title and content
     */
    async translateReport(title, content) {
        if (this.isTranslating) return null;
        
        this.isTranslating = true;

        try {
            const response = await fetch('/translate/report', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.$csrfToken
                },
                body: JSON.stringify({ title, content })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            return data.data;
        } catch (error) {
            console.error('Translation error:', error);
            throw error;
        } finally {
            this.isTranslating = false;
        }
    }

    /**
     * Translate single text
     */
    async translateText(text) {
        try {
            const response = await fetch('/translate/text', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.$csrfToken
                },
                body: JSON.stringify({ text })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error);
            }

            return data.data.translated;
        } catch (error) {
            console.error('Translation error:', error);
            throw error;
        }
    }
}

/**
 * Usage Example in your view/form:
 * 
 * <button type="button" id="translateBtn" class="btn btn-secondary">
 *     Translate to Bangla
 * </button>
 * 
 * <div id="translationResult" style="display:none;">
 *     <h4>Bangla Version:</h4>
 *     <p><strong>Title:</strong> <span id="titleBn"></span></p>
 *     <p><strong>Content:</strong> <span id="contentBn"></span></p>
 * </div>
 * 
 * JavaScript:
 * 
 * import { ReportTranslator } from '/path/to/this/file.js';
 * 
 * const translator = new ReportTranslator(
 *     document.querySelector('meta[name="csrf-token"]').content
 * );
 * 
 * document.getElementById('translateBtn').addEventListener('click', async () => {
 *     const title = document.getElementById('title').value;
 *     const content = document.getElementById('content').value;
 *     
 *     try {
 *         const result = await translator.translateReport(title, content);
 *         document.getElementById('titleBn').textContent = result.title_bn;
 *         document.getElementById('contentBn').textContent = result.content_bn;
 *         document.getElementById('translationResult').style.display = 'block';
 *     } catch (error) {
 *         alert('Translation failed: ' + error.message);
 *     }
 * });
 */
