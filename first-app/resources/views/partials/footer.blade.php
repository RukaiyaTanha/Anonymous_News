<footer class="site-footer">
    <div class="site-footer-inner">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-brand-copy">
                    <h4>{{ config('app.name', 'ANP') }}</h4>
                    <p>VERIFIED COMMUNITY REPORTING</p>
                </div>
                <p class="footer-brand-text">{{ __('Transparent, credible, and community-driven. Powered by AI and human moderation.') }}</p>
                <div class="footer-socials">
                    <a href="#" aria-label="Twitter">f</a>
                    <a href="#" aria-label="LinkedIn">in</a>
                    <a href="#" aria-label="Email">@</a>
                </div>
            </div>

            <div class="footer-column">
                <h5>{{ __('Product') }}</h5>
                <ul>
                    <li><a href="{{ route('reports.create') }}">{{ __('Submit Report') }}</a></li>
                    <li><a href="{{ route('news.index') }}">{{ __('Browse News') }}</a></li>
                    <li><a href="{{ route('home') }}">{{ __('Verified Reports') }}</a></li>
                    <li><a href="#">{{ __('API Docs') }}</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h5>{{ __('Company') }}</h5>
                <ul>
                    <li><a href="#">{{ __('About Us') }}</a></li>
                    <li><a href="#">{{ __('How It Works') }}</a></li>
                    <li><a href="#">{{ __('Our Team') }}</a></li>
                    <li><a href="#">{{ __('Careers') }}</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h5>{{ __('Legal') }}</h5>
                <ul>
                    <li><a href="#">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="#">{{ __('Terms of Service') }}</a></li>
                    <li><a href="#">{{ __('Cookie Policy') }}</a></li>
                    <li><a href="#">{{ __('Contact') }}</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h5>{{ __('Get in Touch') }}</h5>
                <div>
                    <p>{{ __('📍 Location') }}</p>
                    <strong>Dhaka, Bangladesh</strong>
                </div>
                <div>
                    <p>{{ __('✉️ Email') }}</p>
                    <a href="mailto:tanharukiya@gmail.com">tanharukiya@gmail.com</a>
                </div>
                <div>
                    <p>{{ __('🕐 Support') }}</p>
                    <strong>24/7 Available</strong>
                </div>
            </div>
        </div>

        <div class="footer-stats">
            <div class="footer-developer">
                <p>{{ __('Developed By') }}</p>
                <strong>{{ __('@Rukaiya Tanha') }}</strong>
                <span>{{ __('Building trust in journalism') }}</span>
            </div>
            <div class="footer-stat">
                <strong>1,248+</strong>
                <span>{{ __('Reports Submitted') }}</span>
            </div>
            <div class="footer-stat footer-stat-right">
                <strong>412</strong>
                <span>{{ __('Verified & Published') }}</span>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 {{ config('app.name', 'Anonymous News Portal') }}. {{ __('All rights reserved.') }}</p>
            <div>
                <span>{{ __('v1.0.0') }}</span>
            </div>
        </div>
    </div>
</footer>
