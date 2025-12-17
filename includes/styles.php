<style>
:root {
    --primary: #059669;
    --primary-dark: #047857;
    --primary-light: #10b981;
    --accent: #f59e0b;
    --bg: #fefefe;
    --bg-alt: #f8fafc;
    --text: #0f172a;
    --text-light: #64748b;
    --border: #e2e8f0;
    --success: #10b981;
    --error: #ef4444;
    --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
}

body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-alt);
    color: var(--text);
    line-height: 1.6;
    padding-top: 72px;
}

/* Header Styles */
.main-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 72px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(24px);
    border-bottom: 1px solid rgba(5, 150, 105, 0.1);
    box-shadow: 0 2px 24px rgba(5, 150, 105, 0.08), 0 1px 0 rgba(255, 255, 255, 0.8) inset;
    z-index: 1000;
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.logo a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--primary);
    transition: all 0.3s ease;
}

.logo-icon {
    font-size: 2rem;
    filter: drop-shadow(0 2px 4px rgba(5, 150, 105, 0.3));
}

.logo-text {
    font-weight: 800;
    font-size: 1.5rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-menu {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-left: auto;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--text-light);
    font-weight: 500;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary);
    transform: scaleX(0);
    transition: transform 0.2s ease;
}

.nav-link:hover {
    background: rgba(5, 150, 105, 0.08);
    color: var(--primary);
    transform: translateY(-1px);
}

.nav-link:hover::before {
    transform: scaleX(1);
}

.nav-link svg {
    transition: all 0.2s ease;
}

.nav-link:hover svg {
    transform: scale(1.1);
}

.sell-btn {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25);
    border: none;
}

.sell-btn::before {
    background: rgba(255, 255, 255, 0.2);
}

.sell-btn:hover {
    background: linear-gradient(135deg, var(--primary-dark), var(--primary));
    color: white;
    box-shadow: 0 4px 16px rgba(5, 150, 105, 0.35);
    transform: translateY(-2px);
}

.search-field {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: rgba(5, 150, 105, 0.05);
    border: 1px solid rgba(5, 150, 105, 0.15);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    flex: 1;
    min-width: 200px;
    max-width: 350px;
    transition: all 0.2s ease;
}

.search-field:focus-within {
    background: rgba(255, 255, 255, 0.9);
    border-color: var(--primary);
    box-shadow: 0 2px 12px rgba(5, 150, 105, 0.15);
}

.search-field svg {
    color: var(--text-light);
    flex-shrink: 0;
}

.search-field:focus-within svg {
    color: var(--primary);
}

.search-field input {
    border: none;
    outline: none;
    background: transparent;
    flex: 1;
    font-size: 0.9rem;
    color: var(--text);
    font-weight: 500;
}

.search-field input::placeholder {
    color: var(--text-light);
    font-weight: 400;
}

.search-field {
    position: relative;
}

.search-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    display: none;
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

.quick-result {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--bg-alt);
}

.quick-result:last-child {
    border-bottom: none;
}

.quick-result:hover {
    background: var(--bg-alt);
}

.result-icon {
    font-size: 1.25rem;
    width: 40px;
    height: 40px;
    background: rgba(5, 150, 105, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.result-info {
    flex: 1;
}

.result-name {
    font-weight: 600;
    color: var(--text);
    font-size: 0.9rem;
}

.result-price {
    font-size: 0.8rem;
    color: var(--primary);
    font-weight: 600;
}

.result-seller, .result-role {
    font-size: 0.75rem;
    color: var(--text-light);
    margin-top: 0.125rem;
}

.result-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.search-loading {
    padding: 1rem;
    text-align: center;
    color: var(--text-light);
    font-size: 0.875rem;
}

.profile-body {
    padding-top: 0;
}

/* Desktop Profile */
.desktop-profile {
    display: block;
}

.mobile-profile {
    display: none;
}

.profile-banner {
    height: 200px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    position: relative;
    overflow: hidden;
}

.banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, rgba(5, 150, 105, 0.8), rgba(16, 185, 129, 0.6));
}

.profile-header-desktop {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 2rem;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.4));
}

.profile-main-info {
    display: flex;
    align-items: flex-end;
    gap: 1.5rem;
    flex: 1;
}

.profile-avatar-desktop {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 3rem;
    overflow: hidden;
    border: 4px solid white;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    flex-shrink: 0;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.profile-avatar-desktop:hover {
    transform: scale(1.05);
}

.profile-avatar-desktop img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info {
    color: white;
    padding-bottom: 0.5rem;
}

.name-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.name-section h1 {
    font-size: 2.25rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
    line-height: 1.1;
}

.verified-badge-desktop {
    background: rgba(255, 255, 255, 0.95);
    color: var(--primary);
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.unverified-badge-desktop {
    background: rgba(255, 255, 255, 0.95);
    color: #ef4444;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.role-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 1rem;
    backdrop-filter: blur(10px);
}

.profile-stats {
    display: flex;
    gap: 2rem;
}

.stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}

.stat-label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
}

.profile-actions-desktop {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}

.btn-primary, .btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-block;
}

.btn-primary {
    background: white;
    color: var(--primary);
}

.btn-primary:hover {
    background: var(--bg-alt);
    transform: translateY(-2px);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    backdrop-filter: blur(10px);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
}

.desktop-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
}

.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.info-section h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 1rem;
    border-bottom: 2px solid var(--primary);
    padding-bottom: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--bg-alt);
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-icon {
    font-size: 1.25rem;
    width: 35px;
    text-align: center;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--bg-alt);
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item strong {
    color: var(--text);
    font-weight: 600;
}

.detail-item span {
    color: var(--text-light);
}

.main-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.products-desktop h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary);
    padding-bottom: 0.5rem;
}

.products-desktop-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.product-desktop-card {
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
}

.product-desktop-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.product-desktop-image {
    height: 200px;
    background: var(--bg-alt);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-desktop-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-desktop-placeholder {
    font-size: 3rem;
    color: var(--text-light);
}

.product-desktop-details {
    padding: 1.5rem;
}

.product-desktop-details h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.5rem;
}

.product-desktop-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.product-desktop-category {
    font-size: 0.875rem;
    color: var(--text-light);
    background: var(--bg-alt);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    display: inline-block;
    margin-bottom: 0.75rem;
}

.product-desktop-desc {
    font-size: 0.9rem;
    color: var(--text-light);
    line-height: 1.4;
}

/* Mobile Profile */
@media (max-width: 768px) {
    .desktop-profile {
        display: none;
    }
    
    .mobile-profile {
        display: block;
        background: var(--bg-alt);
    }
    
    .mobile-header {
        background: white;
        margin-bottom: 8px;
    }
    
    .cover-section {
        position: relative;
        height: 160px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
    }
    
    .cover-bg {
        position: absolute;
        inset: 0;
        background: linear-gradient(45deg, rgba(5, 150, 105, 0.1), rgba(16, 185, 129, 0.05));
    }
    
    .banner-content {
        position: absolute;
        top: 20px;
        left: 20px;
        right: 20px;
        text-align: center;
    }
    
    .banner-name {
        font-size: 1.8rem;
        font-weight: 800;
        color: white;
        margin: 0;
        text-shadow: 0 3px 8px rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        line-height: 1.1;
    }
    
    .profile-section {
        position: absolute;
        bottom: -50px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: center;
    }
    
    .profile-pic {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 2.5rem;
        overflow: hidden;
        border: 4px solid white;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .profile-pic:hover {
        transform: scale(1.05);
    }
    
    .profile-pic img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .unverified-badge {
        background: rgba(255, 255, 255, 0.9);
        color: #ef4444;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .verified-badge {
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary);
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .role-row, .location-row {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 3px;
    }
    
    .role-icon, .location-icon {
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    
    .role-text {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
    }
    
    .location-text {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 400;
        line-height: 1.2;
    }
    
    .action-buttons {
        padding: 50px 15px 15px;
        display: flex;
        gap: 10px;
    }
    
    .action-btn {
        flex: 1;
        padding: 12px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: center;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    
    .action-btn.primary {
        background: var(--primary);
        color: white;
    }
    
    .action-btn.primary:hover {
        background: var(--primary-dark);
    }
    
    .action-btn.secondary {
        background: var(--bg-alt);
        color: var(--text);
        border: 1px solid var(--border);
    }
    
    .action-btn.secondary:hover {
        background: rgba(5, 150, 105, 0.1);
        border-color: var(--primary);
    }
    
    .mobile-content {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 0 8px;
    }
    
    .content-section {
        background: white;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .section-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
    }
    
    .section-title h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text);
        margin: 0;
    }
    
    .post-count {
        font-size: 0.85rem;
        color: var(--text-light);
        font-weight: 500;
    }
    
    .about-cards {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .about-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: var(--bg-alt);
        border-radius: 10px;
        border-left: 3px solid var(--primary);
    }
    
    .card-icon {
        width: 32px;
        height: 32px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    
    .card-content {
        flex: 1;
    }
    
    .card-label {
        font-size: 0.8rem;
        color: var(--text-light);
        font-weight: 500;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .card-value {
        font-size: 0.95rem;
        color: var(--text);
        font-weight: 600;
        line-height: 1.3;
    }
    
    .posts-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2px;
    }
    
    .empty-posts {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-light);
    }
    
    .empty-posts-desktop {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--text-light);
    }
    
    .empty-icon {
        font-size: 3rem;
        margin-bottom: 0.5rem;
    }
    
    .empty-posts p, .empty-posts-desktop p {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .post-thumbnail {
        aspect-ratio: 1;
        background: var(--bg-alt);
        overflow: hidden;
        cursor: pointer;
    }
    
    .post-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
    }
    
    .post-thumbnail:hover img {
        transform: scale(1.05);
    }
    
    .post-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--text-light);
        background: var(--bg-alt);
    }
    
    .mobile-banner {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        padding: 2rem 1rem;
        text-align: center;
        color: white;
    }
    
    .mobile-banner-content {
        max-width: 400px;
        margin: 0 auto;
    }
    
    .mobile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 2.5rem;
        overflow: hidden;
        border: 4px solid rgba(255, 255, 255, 0.3);
        margin: 0 auto 1rem;
        backdrop-filter: blur(10px);
    }
    
    .mobile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .mobile-banner h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .mobile-role {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 0.75rem;
        backdrop-filter: blur(10px);
    }
    
    .mobile-location {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }
    
    .mobile-widgets {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .widget {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .widget-header {
        background: linear-gradient(135deg, rgba(5, 150, 105, 0.1), rgba(5, 150, 105, 0.05));
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    
    .widget-header h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text);
        margin: 0;
    }
    
    .product-count {
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .widget-content {
        padding: 1.5rem;
    }
    
    .contact-ctas {
        display: flex;
        gap: 1rem;
    }
    
    .cta-btn {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        background: var(--primary);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .cta-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .cta-icon {
        font-size: 1.5rem;
    }
    
    .posts-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
    
    .post-item {
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        background: var(--bg-alt);
        cursor: pointer;
    }
    
    .post-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .post-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--text-light);
    }
    
    .verified-badge {
        background: var(--primary);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
        margin-left: 0.5rem;
        vertical-align: middle;
    }
    
    .mobile-avatar {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .mobile-avatar:hover {
        transform: scale(1.05);
    }
}

/* Image Viewer */
.image-viewer {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.image-viewer-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.image-viewer-content img {
    width: 100%;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.close-viewer {
    position: absolute;
    top: -40px;
    right: 0;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    backdrop-filter: blur(10px);
    transition: all 0.2s ease;
}

.close-viewer:hover {
    background: rgba(255, 255, 255, 0.3);
}
    
    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--bg-alt);
    }
    
    .stat-row:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        color: var(--text-light);
        font-weight: 500;
    }
    
    .stat-value {
        color: var(--primary);
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--bg-alt);
        border-radius: 12px;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    
    .product-item:last-child {
        margin-bottom: 0;
    }
    
    .product-item:hover {
        background: rgba(5, 150, 105, 0.05);
        transform: translateX(4px);
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-image span {
        font-size: 1.5rem;
    }
    
    .product-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 0.25rem;
    }
    
    .product-price {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }
    
    .product-category {
        font-size: 0.75rem;
        color: var(--text-light);
        background: white;
        padding: 0.25rem 0.5rem;
        border-radius: 8px;
        display: inline-block;
    }
}

/* Facebook-style Profile */
.fb-profile {
    max-width: 1200px;
    margin: 0 auto;
    background: var(--bg-alt);
}

.cover-photo {
    height: 300px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    position: relative;
    border-radius: 0 0 12px 12px;
}

.cover-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, rgba(5, 150, 105, 0.8), rgba(16, 185, 129, 0.6));
    border-radius: 0 0 12px 12px;
}

.profile-main {
    background: white;
    margin: -50px 1rem 0;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 10;
}

.profile-top {
    display: flex;
    align-items: flex-end;
    padding: 1.5rem 2rem 1rem;
    gap: 1.5rem;
}

.profile-avatar-fb {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 4rem;
    overflow: hidden;
    border: 6px solid white;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    margin-top: -75px;
    flex-shrink: 0;
}

.profile-avatar-fb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-name-section {
    flex: 1;
    padding-top: 1rem;
}

.profile-name-section h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 0.25rem;
    line-height: 1.2;
}

.profile-subtitle {
    font-size: 1.1rem;
    color: var(--text-light);
    font-weight: 500;
}

.profile-actions {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    padding-bottom: 1rem;
}

.btn-contact {
    background: var(--primary);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-contact:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.profile-nav {
    display: flex;
    border-top: 1px solid var(--border);
    padding: 0 2rem;
}

.nav-tab {
    background: none;
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    color: var(--text-light);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
}

.nav-tab.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.nav-tab:hover {
    background: var(--bg-alt);
}

.profile-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 1.5rem;
    padding: 1.5rem 1rem;
}

.content-left, .content-right {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.info-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 1rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--bg-alt);
}

.info-item:last-child {
    border-bottom: none;
}

.info-icon {
    font-size: 1.25rem;
    width: 40px;
    height: 40px;
    background: rgba(5, 150, 105, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-item strong {
    font-weight: 600;
    color: var(--text);
    font-size: 0.9rem;
    display: block;
    margin-bottom: 0.25rem;
}

.info-item p {
    color: var(--text-light);
    font-size: 0.9rem;
    margin: 0;
}

.products-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
}

.product-count {
    font-size: 0.875rem;
    color: var(--text-light);
    background: var(--bg-alt);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
}

.products-fb-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.product-fb-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s ease;
    cursor: pointer;
}

.product-fb-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.product-fb-image {
    height: 150px;
    background: var(--bg-alt);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-fb-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-fb-placeholder {
    font-size: 2rem;
    color: var(--text-light);
}

.product-fb-info {
    padding: 1rem;
}

.product-fb-info h4 {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.product-fb-price {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0;
}

@media (max-width: 768px) {
    .desktop-profile {
        display: none;
    }
    
    .cover-photo {
        height: 200px;
    }
    
    .profile-main {
        margin: -30px 0.5rem 0;
    }
    
    .profile-top {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1rem;
        gap: 1rem;
    }
    
    .profile-avatar-fb {
        width: 120px;
        height: 120px;
        font-size: 3rem;
        margin-top: -60px;
    }
    
    .profile-name-section h1 {
        font-size: 2rem;
    }
    
    .profile-nav {
        padding: 0 1rem;
    }
    
    .nav-tab {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .profile-content {
        grid-template-columns: 1fr;
        padding: 1rem 0.5rem;
    }
    
    .info-card, .products-section {
        padding: 1rem;
    }
    
    .products-fb-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .desktop-content {
        grid-template-columns: 1fr;
        padding: 1rem;
    }
    
    .sidebar {
        order: 2;
    }
    
    .main-content {
        order: 1;
        margin-bottom: 1rem;
    }
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
}

.user-profile:hover {
    background: rgba(5, 150, 105, 0.05);
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    text-align: right;
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text);
    line-height: 1.2;
}

.user-role {
    font-size: 0.75rem;
    color: var(--text-light);
    font-weight: 500;
}

.user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    overflow: hidden;
    border: 3px solid white;
    box-shadow: 0 4px 16px rgba(5, 150, 105, 0.2);
    transition: all 0.3s ease;
}

.user-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 24px rgba(5, 150, 105, 0.3);
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.auth-buttons {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.btn-login, .btn-register {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.btn-login {
    color: var(--text);
    border-color: var(--border);
    background: white;
}

.btn-login:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.btn-register {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
    box-shadow: 0 4px 16px rgba(5, 150, 105, 0.3);
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(5, 150, 105, 0.4);
}

.user-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    min-width: 220px;
    display: none;
    z-index: 1001;
    overflow: hidden;
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
}

.full-name {
    font-weight: 600;
    color: var(--text);
}

.user-type {
    font-size: 0.75rem;
    color: var(--text-light);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: var(--text);
    transition: background 0.2s ease;
}

.dropdown-item:hover {
    background: var(--bg-alt);
}

.dropdown-divider {
    height: 1px;
    background: var(--border);
    margin: 0.5rem 0;
}

/* Search Popup */
.search-popup {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: none;
}

.search-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.search-content {
    position: absolute;
    top: 10%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow: hidden;
}

.search-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border);
}

.search-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-light);
    padding: 0.25rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background: var(--bg-alt);
    color: var(--text);
}

.search-body {
    padding: 2rem;
}

.search-input-group {
    display: flex;
    align-items: center;
    background: var(--bg-alt);
    border: 2px solid var(--border);
    border-radius: 16px;
    padding: 1rem 1.25rem;
    gap: 1rem;
    transition: all 0.3s ease;
    margin-bottom: 2rem;
}

.search-input-group:focus-within {
    border-color: var(--primary);
    background: white;
    box-shadow: 0 4px 20px rgba(5, 150, 105, 0.15);
}

.search-input-group svg {
    color: var(--text-light);
    flex-shrink: 0;
}

.search-input-group input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1rem;
    background: transparent;
    color: var(--text);
}

.search-input-group input::placeholder {
    color: var(--text-light);
}

.search-categories h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 1rem;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--bg-alt);
    border-radius: 12px;
    text-decoration: none;
    color: var(--text);
    font-weight: 500;
    transition: all 0.3s ease;
}

.category-item:hover {
    background: rgba(5, 150, 105, 0.1);
    color: var(--primary);
    transform: translateY(-2px);
}

.search-results {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 2rem;
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 12px;
    text-decoration: none;
    color: var(--text);
    transition: all 0.2s ease;
    margin-bottom: 0.5rem;
}

.search-result-item:hover {
    background: var(--bg-alt);
}



.user-menu {
    position: relative;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    cursor: pointer;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.auth-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-login, .btn-register {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.btn-login {
    color: var(--text);
    border: 1px solid var(--border);
}

.btn-register {
    background: var(--primary);
    color: white;
}



.mobile-nav-overlay {
    position: fixed;
    top: 64px;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    z-index: 999;
}

.mobile-nav {
    background: white;
    height: 100%;
    width: 280px;
    padding: 1rem;
}

.mobile-search {
    margin-bottom: 2rem;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    text-decoration: none;
    color: var(--text);
    border-radius: 8px;
    margin-bottom: 0.5rem;
    transition: background 0.2s ease;
}

.mobile-nav-link:hover {
    background: var(--bg);
}

.desktop-only {
    display: block;
}

@media (max-width: 768px) {
    .header-container {
        padding: 0 1rem;
        gap: 1rem;
    }
    
    .logo-text {
        display: none;
    }
    
    .logo-icon {
        font-size: 1.75rem;
    }
    
    .header-container {
        padding: 0 0.75rem;
        gap: 0.5rem;
    }
    
    .logo {
        flex-shrink: 0;
    }
    
    .search-field {
        min-width: 120px;
        max-width: none;
        flex: 1;
        padding: 0.625rem 0.75rem;
        gap: 0.5rem;
    }
    
    .search-field input {
        font-size: 0.875rem;
    }
    
    .nav-menu {
        display: none;
    }
    
    .user-section {
        flex-shrink: 0;
    }
    
    .desktop-only {
        display: none;
    }
    
    .user-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.9rem;
    }
    
    .btn-login {
        display: none;
    }
    
    .btn-register {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .search-content {
        top: 5%;
        width: 95%;
        max-height: 90vh;
    }
    
    .search-header {
        padding: 1rem 1.5rem;
    }
    
    .search-body {
        padding: 1.5rem;
    }
    
    .category-grid {
        grid-template-columns: 1fr;
    }
    
    .user-dropdown {
        right: -0.5rem;
        min-width: 180px;
    }
}
</style>

<script>
function openSearch() {
    const popup = document.getElementById('searchPopup');
    popup.style.display = 'block';
    setTimeout(() => {
        document.getElementById('searchInput').focus();
    }, 100);
}

function closeSearch() {
    const popup = document.getElementById('searchPopup');
    popup.style.display = 'none';
    document.getElementById('searchInput').value = '';
    document.getElementById('searchResults').innerHTML = '';
}

function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Quick search functionality
document.addEventListener('DOMContentLoaded', function() {
    const quickSearch = document.getElementById('quickSearch');
    let searchTimeout;
    
    if (quickSearch) {
        quickSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performQuickSearch(query);
                }, 300);
            } else {
                hideSearchResults();
            }
        });
        
        quickSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    performQuickSearch(query);
                }
            }
        });
    }
});

function performQuickSearch(query) {
    showSearchResults(`<div class="search-loading">Searching...</div>`);
    
    fetch(`/curuzamuhinzi/api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                showSearchResults(`<div class="search-loading">No results found</div>`);
                return;
            }
            
            let html = '';
            data.forEach(item => {
                if (item.type === 'product') {
                    html += `
                        <div class="quick-result" onclick="window.location.href='${item.url}'">
                            <span class="result-icon">${item.image}</span>
                            <div class="result-info">
                                <div class="result-name">${item.name}</div>
                                <div class="result-price">${item.price}</div>
                                <div class="result-seller">by ${item.seller}</div>
                            </div>
                        </div>
                    `;
                } else {
                    const userImage = item.image.startsWith('/') ? 
                        `<img src="${item.image}" alt="Profile">` : 
                        item.image;
                    html += `
                        <div class="quick-result" onclick="window.location.href='${item.url}'">
                            <span class="result-icon">${userImage}</span>
                            <div class="result-info">
                                <div class="result-name">${item.name}</div>
                                <div class="result-role">${item.role}</div>
                            </div>
                        </div>
                    `;
                }
            });
            showSearchResults(html);
        })
        .catch(error => {
            console.error('Search error:', error);
            showSearchResults(`<div class="search-loading">No results found</div>`);
        });
}

function showSearchResults(html) {
    let dropdown = document.getElementById('searchDropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'searchDropdown';
        dropdown.className = 'search-dropdown';
        document.querySelector('.search-field').appendChild(dropdown);
    }
    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

function hideSearchResults() {
    const dropdown = document.getElementById('searchDropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
}



// Close popup with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSearch();
    }
});



// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-section')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) dropdown.style.display = 'none';
    }
    if (!e.target.closest('.search-field')) {
        hideSearchResults();
    }
});
</script>