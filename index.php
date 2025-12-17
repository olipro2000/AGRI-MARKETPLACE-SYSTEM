<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curuza Muhinzi - Rwanda's Agricultural Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.7;
            color: var(--text);
            background: var(--bg);
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            color: white;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--primary);
            background: rgba(5, 150, 105, 0.1);
            transform: translateY(-2px);
        }
        
        .nav-links a.active {
            color: var(--primary);
            background: rgba(5, 150, 105, 0.15);
        }
        
        .auth-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .mobile-menu {
            display: none;
            background: var(--primary);
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 8px;
            cursor: pointer;
            color: white;
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .mobile-menu:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }
        
        .hamburger {
            position: relative;
            width: 20px;
            height: 2px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .hamburger::before,
        .hamburger::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 2px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .hamburger::before {
            top: -6px;
        }
        
        .hamburger::after {
            top: 6px;
        }
        
        .mobile-menu.active .hamburger {
            background: transparent;
        }
        
        .mobile-menu.active .hamburger::before {
            transform: rotate(45deg);
            top: 0;
        }
        
        .mobile-menu.active .hamburger::after {
            transform: rotate(-45deg);
            top: 0;
        }
        
        .mobile-nav {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-20px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
        }
        
        .mobile-nav.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .mobile-nav-links {
            list-style: none;
            padding: 1.5rem 1rem 0;
        }
        
        .mobile-nav-item {
            margin-bottom: 0.5rem;
            transform: translateX(-30px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .mobile-nav.active .mobile-nav-item {
            transform: translateX(0);
            opacity: 1;
        }
        
        .mobile-nav.active .mobile-nav-item:nth-child(1) { transition-delay: 0.1s; }
        .mobile-nav.active .mobile-nav-item:nth-child(2) { transition-delay: 0.15s; }
        .mobile-nav.active .mobile-nav-item:nth-child(3) { transition-delay: 0.2s; }
        .mobile-nav.active .mobile-nav-item:nth-child(4) { transition-delay: 0.25s; }
        
        .mobile-nav-link {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .mobile-nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(5, 150, 105, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .mobile-nav-link:hover::before {
            left: 100%;
        }
        
        .mobile-nav-link:hover {
            background: rgba(5, 150, 105, 0.05);
            color: var(--primary);
            transform: translateX(5px);
        }
        
        .nav-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            color: white;
            flex-shrink: 0;
        }
        
        .nav-content {
            flex: 1;
        }
        
        .nav-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text);
        }
        
        .nav-desc {
            font-size: 0.8rem;
            color: var(--text-light);
            line-height: 1.3;
        }
        
        .mobile-auth {
            padding: 1.5rem 1rem;
            border-top: 1px solid var(--border);
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }
        
        .auth-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .auth-buttons-mobile {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        .hero-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 2s ease-in-out;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .slide.active {
            opacity: 0.4;
        }
        
        .slide-1 {
            background-image: url('https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=1920&h=1080&fit=crop');
        }
        
        .slide-2 {
            background-image: url('https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920&h=1080&fit=crop');
        }
        
        .slide-3 {
            background-image: url('https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1920&h=1080&fit=crop');
        }
        
        .slide-4 {
            background-image: url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=1920&h=1080&fit=crop');
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.9) 0%, rgba(15, 23, 42, 0.8) 100%);
            z-index: 2;
        }
        
        .hero-content {
            position: relative;
            z-index: 3;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .hero-text {
            color: white;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 900;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }
        
        .hero-title .highlight {
            background: linear-gradient(135deg, #10b981, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3rem;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }
        
        .hero-visual {
            position: relative;
        }
        
        .hero-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            transform: perspective(1000px) rotateY(-15deg) rotateX(10deg);
        }
        
        .hero-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            animation: cardFloat 6s ease-in-out infinite;
        }
        
        .hero-card:nth-child(1) { animation-delay: 0s; }
        .hero-card:nth-child(2) { animation-delay: 1.5s; }
        .hero-card:nth-child(3) { animation-delay: 3s; }
        .hero-card:nth-child(4) { animation-delay: 4.5s; }
        
        @keyframes cardFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .hero-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .card-desc {
            font-size: 0.875rem;
            color: var(--text-light);
            line-height: 1.5;
        }
        

        
        .slide-indicators {
            position: absolute;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            gap: 0.5rem;
            z-index: 4;
        }
        
        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .indicator.active {
            background: white;
            transform: scale(1.2);
        }
        
        /* Features Section */
        .features {
            padding: 6rem 0;
            background: var(--bg);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .section-header.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .section-badge {
            display: inline-block;
            background: rgba(245, 158, 11, 0.1);
            color: var(--accent);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text);
        }
        
        .section-subtitle {
            color: var(--text-light);
            font-size: 1.125rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            text-align: left;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(50px);
        }
        
        .feature-card.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        
        .feature-card.animate:hover {
            transform: translateY(-8px);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .feature-title {
            font-size: 1.375rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text);
        }
        
        .feature-description {
            color: var(--text-light);
            line-height: 1.7;
            font-size: 1rem;
        }
        
        /* How It Works */
        .how-it-works {
            padding: 6rem 0;
            background: var(--bg-alt);
        }
        
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .step-card {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            text-align: center;
            position: relative;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .step-number {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.125rem;
            box-shadow: var(--shadow);
        }
        
        .step-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            margin-top: 1.5rem;
            color: var(--text);
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 6rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .cta-content {
            position: relative;
            z-index: 2;
        }
        
        .cta-title {
            font-size: clamp(2rem, 4vw, 2.75rem);
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .cta-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-white {
            background: white;
            color: var(--primary);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-white:hover {
            background: var(--bg-alt);
            transform: translateY(-3px);
        }
        
        .btn-outline-white {
            background: transparent;
            color: white;
            border: 2px solid white;
            box-shadow: none;
        }
        
        .btn-outline-white:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* About Section */
        .about {
            padding: 6rem 0;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        }
        
        .about-content {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        
        .about-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
        }
        
        .about-stat {
            text-align: center;
        }
        
        .about-stat .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }
        
        .about-stat .stat-text {
            font-size: 0.875rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .about-features {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .about-feature {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .about-feature.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .about-feature:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .about-feature.animate:hover {
            transform: translateY(-2px);
        }
        
        .about-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .about-stats.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .about-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }
        
        .about-info h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .about-info p {
            color: var(--text-light);
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .about-visual {
            position: relative;
        }
        
        .about-slider {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            height: 500px;
        }
        
        .about-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        
        .about-slide.active {
            opacity: 1;
        }
        
        .about-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .about-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 2rem;
            color: white;
        }
        
        .overlay-content h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .overlay-content p {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .about-slide-indicators {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .about-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .about-indicator.active {
            background: white;
            transform: scale(1.2);
        }
        
        /* Contact Section */
        .contact {
            padding: 6rem 0;
            background: var(--bg-alt);
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .contact-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .contact-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .contact-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .contact-card p {
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        .contact-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        
        .contact-link:hover {
            text-decoration: underline;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
        }
        
        .footer-top {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 4rem;
            padding: 4rem 0 2rem;
        }
        
        .footer-brand {
            max-width: 400px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .footer-logo .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .footer-logo .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
        }
        
        .footer-desc {
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .footer-btn {
            background: var(--primary);
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .footer-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .footer-links {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        
        .footer-section h4 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }
        
        .footer-section a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }
        
        .footer-section a:hover {
            color: var(--primary-light);
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        
        .contact-icon {
            font-size: 1rem;
        }
        
        .contact-item a {
            margin: 0;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 2rem 0;
        }
        
        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .footer-bottom p {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
        }
        
        .footer-badges {
            display: flex;
            gap: 1rem;
        }
        
        .badge {
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.8);
        }
        
        /* Preloader */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .preloader-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: logoFloat 2s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .preloader-title {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .preloader-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .loading-dots {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            background: white;
            border-radius: 50%;
            animation: dotBounce 1.4s ease-in-out infinite both;
        }
        
        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        .dot:nth-child(3) { animation-delay: 0s; }
        
        @keyframes dotBounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }
        
        .progress-container {
            width: 300px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .progress-fill {
            height: 100%;
            background: white;
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 2px;
        }
        
        .progress-text {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            text-align: center;
        }
        
        body.loading {
            overflow: hidden;
        }
        
        /* Mobile Responsive */
        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-content {
                gap: 3rem;
                padding: 0 1.5rem;
            }
        }
        
        @media (max-width: 1024px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu {
                display: block;
            }
            
            .hero-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
                padding: 2rem 1rem;
            }
            
            .hero-visual {
                order: -1;
            }
            
            .hero-cards {
                transform: none;
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
                max-width: 500px;
                margin: 0 auto;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .auth-buttons {
                display: none;
            }
            
            .hero {
                min-height: 80vh;
                padding-top: 80px;
            }
            
            .hero-content {
                padding: 1rem;
                gap: 1rem;
            }
            
            .hero-visual {
                display: none;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
                width: 100%;
            }
            
            .hero-buttons .btn {
                width: 100%;
                max-width: 280px;
            }
            
            .slide-indicators {
                bottom: 1rem;
                right: 50%;
                transform: translateX(50%);
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .feature-card {
                padding: 2rem;
            }
            
            .step-card {
                padding: 2rem;
            }
            
            .about {
                padding: 4rem 0;
            }
            
            .about-content {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .about-visual {
                order: -1;
            }
            
            .about-stats {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 1.5rem;
            }
            
            .about-features {
                gap: 1rem;
            }
            
            .about-feature {
                padding: 1.25rem;
            }
            
            .about-slider {
                height: 300px;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-top {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 3rem 0 1rem;
            }
            
            .footer-links {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-badges {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                padding: 0 0.75rem;
            }
            
            .logo {
                font-size: 1.125rem;
            }
            
            .logo-icon {
                width: 32px;
                height: 32px;
                font-size: 1rem;
            }
            
            .hero {
                min-height: 70vh;
            }
            
            .hero-content {
                padding: 0.75rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
        }
        

    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="preloader-logo">🌱</div>
        <h1 class="preloader-title">Curuza Muhinzi</h1>
        <p class="preloader-subtitle">Rwanda's Agricultural Platform</p>
        
        <div class="loading-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        
        <div class="progress-container">
            <div class="progress-fill" id="progressFill"></div>
        </div>
        <div class="progress-text" id="progressText">Loading 0%</div>
    </div>
    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="#" class="logo">
                <div class="logo-icon">🌱</div>
                <span>Curuza Muhinzi</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            
            <div class="auth-buttons">
                <a href="auth/login.php" class="btn btn-outline">Login</a>
                <a href="auth/register.php" class="btn btn-primary">Join Now</a>
            </div>
            
            <button class="mobile-menu" onclick="toggleMobileNav()" id="mobileMenuBtn">
                <div class="hamburger"></div>
            </button>
        </nav>
        
        <div class="mobile-nav" id="mobileNav">
            <ul class="mobile-nav-links">
                <li class="mobile-nav-item">
                    <a href="#features" class="mobile-nav-link">
                        <div class="nav-icon">✨</div>
                        <div class="nav-content">
                            <div class="nav-title">Features</div>
                            <div class="nav-desc">See what makes us special</div>
                        </div>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="#how-it-works" class="mobile-nav-link">
                        <div class="nav-icon">🚀</div>
                        <div class="nav-content">
                            <div class="nav-title">How It Works</div>
                            <div class="nav-desc">Simple steps to get started</div>
                        </div>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="#about" class="mobile-nav-link">
                        <div class="nav-icon">🌱</div>
                        <div class="nav-content">
                            <div class="nav-title">About Us</div>
                            <div class="nav-desc">Our mission for Rwanda's farmers</div>
                        </div>
                    </a>
                </li>
                <li class="mobile-nav-item">
                    <a href="#contact" class="mobile-nav-link">
                        <div class="nav-icon">📞</div>
                        <div class="nav-content">
                            <div class="nav-title">Contact</div>
                            <div class="nav-desc">Get help when you need it</div>
                        </div>
                    </a>
                </li>
            </ul>
            <div class="mobile-auth">
                <div class="auth-title">Ready to join?</div>
                <div class="auth-buttons-mobile">
                    <a href="auth/login.php" class="btn btn-outline">Login to Account</a>
                    <a href="auth/register.php" class="btn btn-primary">Create Account</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            <div class="slide slide-1 active"></div>
            <div class="slide slide-2"></div>
            <div class="slide slide-3"></div>
            <div class="slide slide-4"></div>
        </div>
        
        <div class="hero-overlay"></div>
        
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    🇷🇼 Rwanda's #1 Agricultural Platform
                </div>
                
                <h1 class="hero-title">
                    Connect <span class="highlight">Rwanda's Farmers</span> & Buyers
                </h1>
                
                <p class="hero-subtitle">
                    The easiest way to buy and sell farm products in Rwanda. 
                    Connect directly with farmers, get fresh produce, and grow your agricultural business.
                </p>
                
                <div class="hero-buttons">
                    <a href="auth/register.php" class="btn btn-primary">
                        🌱 Start Selling
                    </a>
                    <a href="auth/register.php" class="btn btn-white">
                        🛒 Start Buying
                    </a>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="hero-cards">
                    <div class="hero-card">
                        <div class="card-icon">🌽</div>
                        <div class="card-title">Fresh Crops</div>
                        <div class="card-desc">Direct from Rwanda's fertile farms</div>
                    </div>
                    <div class="hero-card">
                        <div class="card-icon">🐄</div>
                        <div class="card-title">Livestock</div>
                        <div class="card-desc">Quality animals and dairy products</div>
                    </div>
                    <div class="hero-card">
                        <div class="card-icon">💰</div>
                        <div class="card-title">Fair Prices</div>
                        <div class="card-desc">Better income for farmers</div>
                    </div>
                    <div class="hero-card">
                        <div class="card-icon">🚚</div>
                        <div class="card-title">Fast Delivery</div>
                        <div class="card-desc">Fresh products to your door</div>
                    </div>
                </div>
            </div>
        </div>
        

        
        <div class="slide-indicators">
            <div class="indicator active" onclick="changeSlide(0)"></div>
            <div class="indicator" onclick="changeSlide(1)"></div>
            <div class="indicator" onclick="changeSlide(2)"></div>
            <div class="indicator" onclick="changeSlide(3)"></div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <div class="section-header">
                        <div class="section-badge">🇷🇼 Our Mission</div>
                        <h2 class="section-title">Empowering Rwanda's Agriculture</h2>
                        <p class="section-subtitle">
                            Connecting farmers directly with buyers to create a stronger, more profitable agricultural ecosystem across all provinces of Rwanda
                        </p>
                    </div>
                    
                    <div class="about-stats">
                        <div class="about-stat">
                            <div class="stat-number">5</div>
                            <div class="stat-text">Provinces Covered</div>
                        </div>
                        <div class="about-stat">
                            <div class="stat-number">30+</div>
                            <div class="stat-text">Districts Reached</div>
                        </div>
                        <div class="about-stat">
                            <div class="stat-number">100%</div>
                            <div class="stat-text">Free to Use</div>
                        </div>
                    </div>
                    
                    <div class="about-features">
                        <div class="about-feature">
                            <div class="about-icon">🌾</div>
                            <div class="about-info">
                                <h4>Direct Farm-to-Market Connection</h4>
                                <p>We eliminate middlemen, allowing farmers to sell directly to buyers and keep more profit from their hard work.</p>
                            </div>
                        </div>
                        
                        <div class="about-feature">
                            <div class="about-icon">📱</div>
                            <div class="about-info">
                                <h4>Mobile-First Platform</h4>
                                <p>Built for smartphones, our platform works perfectly on any device, making it accessible to farmers everywhere.</p>
                            </div>
                        </div>
                        
                        <div class="about-feature">
                            <div class="about-icon">🇷🇼</div>
                            <div class="about-info">
                                <h4>Supporting Local Communities</h4>
                                <p>By keeping trade local, we strengthen communities, reduce transport costs, and support Rwanda's food security.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about-visual">
                    <div class="about-slider">
                        <div class="about-slide active">
                            <img src="https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=600&h=500&fit=crop" alt="Crop Farming" class="about-img">
                            <div class="about-overlay">
                                <div class="overlay-content">
                                    <h4>Fresh Crops</h4>
                                    <p>Quality vegetables and grains from Rwanda's farms</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="about-slide">
                            <img src="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=600&h=500&fit=crop" alt="Livestock Farming" class="about-img">
                            <div class="about-overlay">
                                <div class="overlay-content">
                                    <h4>Healthy Livestock</h4>
                                    <p>Quality cattle and dairy products from local farms</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="about-slide">
                            <img src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=600&h=500&fit=crop" alt="Fresh Produce" class="about-img">
                            <div class="about-overlay">
                                <div class="overlay-content">
                                    <h4>Fresh Produce</h4>
                                    <p>Farm-fresh fruits and vegetables ready for market</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="about-slide">
                            <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&h=500&fit=crop" alt="Agricultural Market" class="about-img">
                            <div class="about-overlay">
                                <div class="overlay-content">
                                    <h4>Market Ready</h4>
                                    <p>Connecting farmers directly with buyers nationwide</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="about-slide-indicators">
                            <div class="about-indicator active" onclick="changeAboutSlide(0)"></div>
                            <div class="about-indicator" onclick="changeAboutSlide(1)"></div>
                            <div class="about-indicator" onclick="changeAboutSlide(2)"></div>
                            <div class="about-indicator" onclick="changeAboutSlide(3)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">✨ Our Benefits</div>
                <h2 class="section-title">Why Farmers & Buyers Love Us</h2>
                <p class="section-subtitle">
                    Simple, direct, and profitable - connecting Rwanda's agricultural community like never before
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3 class="feature-title">Simple & Easy</h3>
                    <p class="feature-description">
                        Built for farmers by farmers. Easy to use on any phone, no complicated setup required.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3 class="feature-title">Direct Trading</h3>
                    <p class="feature-description">
                        Sell directly to buyers. Skip middlemen and keep more profit from your hard work.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3 class="feature-title">Better Income</h3>
                    <p class="feature-description">
                        Get fair prices for your crops. Buyers pay what your products are really worth.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📍</div>
                    <h3 class="feature-title">Local Market</h3>
                    <p class="feature-description">
                        Find buyers near you. Reduce transport costs and sell fresh products quickly.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📞</div>
                    <h3 class="feature-title">Direct Contact</h3>
                    <p class="feature-description">
                        Connect directly with buyers and sellers. Exchange contacts and arrange deals your way.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📞</div>
                    <h3 class="feature-title">Local Support</h3>
                    <p class="feature-description">
                        Help in Kinyarwanda and English. Our team understands farming and is here to help.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">🌱 Simple Steps</div>
                <h2 class="section-title">How to Get Started</h2>
                <p class="section-subtitle">
                    Join Rwanda's farmers and buyers in 4 easy steps. Start connecting today!
                </p>
            </div>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Create Account</h3>
                    <p class="feature-description">
                        Sign up with your phone number or email. It takes less than 2 minutes.
                    </p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Complete Profile</h3>
                    <p class="feature-description">
                        Tell us what you grow or what you want to buy. Add your location.
                    </p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Start Trading</h3>
                    <p class="feature-description">
                        Post your products or browse what's available. Connect with other users.
                    </p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Make Deals</h3>
                    <p class="feature-description">
                        Agree on prices and arrange pickup. Build lasting business relationships.
                    </p>
                </div>
            </div>
        </div>
    </section>


    
    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">📞 Get Help</div>
                <h2 class="section-title">Contact Our Team</h2>
                <p class="section-subtitle">
                    Need help getting started? Our team speaks Kinyarwanda and English
                </p>
            </div>
            
            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-icon">📱</div>
                    <h3>Phone Support</h3>
                    <p>Call us for immediate help</p>
                    <a href="tel:+250788123456" class="contact-link">+250 788 123 456</a>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">✉️</div>
                    <h3>Email Support</h3>
                    <p>Send us your questions</p>
                    <a href="mailto:help@curuzamuhinzi.gov.rw" class="contact-link">help@curuzamuhinzi.gov.rw</a>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">📍</div>
                    <h3>Visit Our Office</h3>
                    <p>Meet us in person</p>
                    <span class="contact-link">Kigali, Rwanda</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <div class="logo-icon">🌱</div>
                        <span class="logo-text">Curuza Muhinzi</span>
                    </div>
                    <p class="footer-desc">
                        Connecting Rwanda's farmers and buyers for a stronger agricultural future. 
                        Supporting local communities across all 5 provinces.
                    </p>
                    <div class="footer-cta">
                        <a href="auth/register.php" class="footer-btn">Join Our Community</a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <div class="footer-section">
                        <h4>Get Started</h4>
                        <a href="auth/register.php">Register as Farmer</a>
                        <a href="auth/register.php">Register as Buyer</a>
                        <a href="auth/login.php">Login to Account</a>
                        <a href="#how-it-works">How It Works</a>
                    </div>
                    
                    <div class="footer-section">
                        <h4>Platform</h4>
                        <a href="#features">Features</a>
                        <a href="#about">About Us</a>
                        <a href="#contact">Contact Support</a>
                        <a href="#help">Help Center</a>
                    </div>
                    
                    <div class="footer-section">
                        <h4>Legal</h4>
                        <a href="#privacy">Privacy Policy</a>
                        <a href="#terms">Terms of Service</a>
                        <a href="#cookies">Cookie Policy</a>
                        <a href="#guidelines">Community Guidelines</a>
                    </div>
                    
                    <div class="footer-section">
                        <h4>Contact Info</h4>
                        <div class="contact-item">
                            <span class="contact-icon">📱</span>
                            <a href="tel:+250788123456">+250 788 123 456</a>
                        </div>
                        <div class="contact-item">
                            <span class="contact-icon">✉️</span>
                            <a href="mailto:help@curuzamuhinzi.gov.rw">help@curuzamuhinzi.gov.rw</a>
                        </div>
                        <div class="contact-item">
                            <span class="contact-icon">📍</span>
                            <span>Kigali, Rwanda</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 Curuza Muhinzi - Government of Rwanda Agricultural Platform</p>
                    <div class="footer-badges">
                        <span class="badge">🇷🇼 Official Platform</span>
                        <span class="badge">🌱 Supporting Farmers</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script>
        function toggleMobileNav() {
            const mobileNav = document.getElementById('mobileNav');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            mobileNav.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
            
            // Prevent body scroll when menu is open
            if (mobileNav.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Close mobile nav when clicking on links
        document.querySelectorAll('.mobile-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                const mobileNav = document.getElementById('mobileNav');
                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                
                mobileNav.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Close mobile nav when clicking outside
        document.addEventListener('click', (e) => {
            const mobileNav = document.getElementById('mobileNav');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            if (!mobileNav.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileNav.classList.remove('active');
                document.getElementById('mobileMenuBtn').classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const mobileNav = document.getElementById('mobileNav');
                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                
                mobileNav.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Smooth scroll for desktop nav links
        document.querySelectorAll('.nav-links a[href^="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const targetPosition = targetSection.offsetTop - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    
                    // Update active state
                    document.querySelectorAll('.nav-links a').forEach(navLink => {
                        navLink.classList.remove('active');
                    });
                    this.classList.add('active');
                }
            });
        });
        
        // Update active nav link on scroll
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const scrollPos = window.scrollY + 100;
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');
                
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    document.querySelectorAll('.nav-links a').forEach(link => {
                        link.classList.remove('active');
                    });
                    
                    const activeLink = document.querySelector(`.nav-links a[href="#${sectionId}"]`);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        });
        
        // Hero slider functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const indicators = document.querySelectorAll('.indicator');
        
        function changeSlide(index) {
            slides[currentSlide].classList.remove('active');
            indicators[currentSlide].classList.remove('active');
            
            currentSlide = index;
            
            slides[currentSlide].classList.add('active');
            indicators[currentSlide].classList.add('active');
        }
        
        function nextSlide() {
            const next = (currentSlide + 1) % slides.length;
            changeSlide(next);
        }
        
        // Auto-slide every 5 seconds
        setInterval(nextSlide, 5000);
        
        // About section slider
        let currentAboutSlide = 0;
        const aboutSlides = document.querySelectorAll('.about-slide');
        const aboutIndicators = document.querySelectorAll('.about-indicator');
        
        function changeAboutSlide(index) {
            aboutSlides[currentAboutSlide].classList.remove('active');
            aboutIndicators[currentAboutSlide].classList.remove('active');
            
            currentAboutSlide = index;
            
            aboutSlides[currentAboutSlide].classList.add('active');
            aboutIndicators[currentAboutSlide].classList.add('active');
        }
        
        function nextAboutSlide() {
            const next = (currentAboutSlide + 1) % aboutSlides.length;
            changeAboutSlide(next);
        }
        
        // Auto-slide about images every 4 seconds
        setInterval(nextAboutSlide, 4000);
        
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);
        
        // Observe elements for animation
        document.querySelectorAll('.section-header, .feature-card, .about-feature, .about-stats').forEach(el => {
            observer.observe(el);
        });
        
        // Preloader functionality
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            let progress = 0;
            
            // Add loading class to body
            document.body.classList.add('loading');
            
            // Simulate loading progress
            const loadingInterval = setInterval(() => {
                progress += Math.random() * 15 + 5;
                
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(loadingInterval);
                    
                    // Hide preloader after completion
                    setTimeout(() => {
                        preloader.classList.add('hidden');
                        document.body.classList.remove('loading');
                        
                        // Remove preloader from DOM after animation
                        setTimeout(() => {
                            preloader.remove();
                        }, 500);
                    }, 500);
                }
                
                progressFill.style.width = progress + '%';
                progressText.textContent = `Loading ${Math.round(progress)}%`;
            }, 100);
            
            // Ensure preloader is hidden after maximum 3 seconds
            setTimeout(() => {
                if (!preloader.classList.contains('hidden')) {
                    clearInterval(loadingInterval);
                    progressFill.style.width = '100%';
                    progressText.textContent = 'Loading 100%';
                    
                    setTimeout(() => {
                        preloader.classList.add('hidden');
                        document.body.classList.remove('loading');
                        
                        setTimeout(() => {
                            preloader.remove();
                        }, 500);
                    }, 200);
                }
            }, 3000);
        });
    </script>
</body>
</html>