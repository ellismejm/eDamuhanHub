
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'eDamuhanHub') | eDamuhanHub</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* =========================================
           GLOBAL VARIABLES
        ========================================= */

        :root {
            --dh-green: #395b32;
            --dh-green-dark: #294526;
            --dh-green-light: #eaf1e5;
            --dh-bg: #f0f2f5;
            --dh-card: #ffffff;
            --dh-text: #1c2b1b;
            --dh-muted: #6b7280;
            --dh-border: #e3e7e2;
            --dh-sidebar-width: 250px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--dh-bg);
            color: var(--dh-text);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        a {
            text-decoration: none;
        }

        /* =========================================
           TOP NAVBAR
        ========================================= */

        .dh-navbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid var(--dh-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }

        .dh-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 240px;
        }

        .dh-brand-icon {
            width: 42px;
            height: 42px;
            background: var(--dh-green);
            color: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .dh-brand-name {
            font-weight: 700;
            font-size: 19px;
            color: var(--dh-green-dark);
            letter-spacing: -0.4px;
        }

        .dh-brand-subtitle {
            font-size: 10px;
            color: var(--dh-muted);
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .dh-search {
            max-width: 520px;
            flex: 1;
            margin: 0 32px;
            position: relative;
        }

        .dh-search input {
            width: 100%;
            border: 1px solid transparent;
            background: #f0f2f5;
            border-radius: 25px;
            padding: 11px 16px 11px 42px;
            outline: none;
            transition: .2s;
        }

        .dh-search input:focus {
            background: #fff;
            border-color: #cbd8c6;
            box-shadow: 0 0 0 3px rgba(57, 91, 50, .08);
        }

        .dh-search i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dh-muted);
        }

        .dh-navbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dh-icon-button {
            width: 40px;
            height: 40px;
            background: #f0f2f5;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
            font-size: 18px;
            position: relative;
        }

        .dh-icon-button:hover {
            background: var(--dh-green-light);
            color: var(--dh-green);
        }

        .dh-notification-dot {
            position: absolute;
            top: 7px;
            right: 7px;
            height: 8px;
            width: 8px;
            background: #dc3545;
            border-radius: 50%;
            border: 1px solid white;
        }

        .dh-user-button {
            border: none;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 5px 8px;
            border-radius: 25px;
        }

        .dh-user-button:hover {
            background: #f0f2f5;
        }

        .dh-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--dh-green-light);
            color: var(--dh-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* =========================================
           SIDEBAR
        ========================================= */

        .dh-sidebar {
            width: var(--dh-sidebar-width);
            position: fixed;
            top: 64px;
            bottom: 0;
            left: 0;
            padding: 20px 14px;
            overflow-y: auto;
            background: var(--dh-bg);
            z-index: 1020;
        }

        .dh-sidebar-label {
            color: #8a9188;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 12px 8px;
        }

        .dh-nav-link {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #475347;
            padding: 11px 13px;
            margin-bottom: 4px;
            border-radius: 10px;
            font-weight: 500;
            transition: .15s ease;
        }

        .dh-nav-link i {
            width: 22px;
            font-size: 18px;
            text-align: center;
        }

        .dh-nav-link:hover {
            background: #e4e9e1;
            color: var(--dh-green-dark);
        }

        .dh-nav-link.active {
            background: var(--dh-green-light);
            color: var(--dh-green-dark);
            font-weight: 700;
        }

        .dh-nav-link.active i {
            color: var(--dh-green);
        }

        .dh-sidebar-footer {
            margin: 24px 8px 10px;
            padding: 15px;
            background: #fff;
            border: 1px solid var(--dh-border);
            border-radius: 12px;
        }

        .dh-sidebar-footer .small {
            color: var(--dh-muted);
            line-height: 1.5;
        }

        /* =========================================
           MAIN CONTENT
        ========================================= */

        .dh-main {
            margin-left: var(--dh-sidebar-width);
            padding: 88px 28px 32px;
            min-height: 100vh;
        }

        .dh-main.dh-guest-main {
            margin-left: 0;
        }

        .dh-page-container {
            max-width: 1500px;
            margin: 0 auto;
        }

        .dh-card {
            background: var(--dh-card);
            border: 1px solid var(--dh-border);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .025);
        }

        .dh-page-title {
            font-size: 25px;
            font-weight: 700;
            letter-spacing: -.5px;
            margin-bottom: 5px;
        }

        .dh-page-subtitle {
            color: var(--dh-muted);
        }

        .dh-btn-primary {
            background: var(--dh-green);
            border-color: var(--dh-green);
            color: #fff;
        }

        .dh-btn-primary:hover {
            background: var(--dh-green-dark);
            border-color: var(--dh-green-dark);
            color: #fff;
        }

        /* =========================================
           DASHBOARD STAT CARDS
        ========================================= */

        .dh-stat-card {
            padding: 20px;
            height: 100%;
        }

        .dh-stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .dh-stat-number {
            font-size: 27px;
            font-weight: 700;
            margin-top: 15px;
            line-height: 1.1;
        }

        .dh-stat-label {
            color: var(--dh-muted);
            margin-top: 5px;
        }

        .dh-section-title {
            font-size: 17px;
            font-weight: 700;
        }

        /* =========================================
           DASHBOARD SPECIES ROWS
        ========================================= */

        .dh-species-image {
            width: 68px;
            height: 68px;
            border-radius: 10px;
            object-fit: cover;
            background: #f1f4ed;
        }

        .dh-species-name {
            color: var(--dh-green-dark);
            font-weight: 700;
        }

        .dh-species-name:hover {
            color: var(--dh-green);
            text-decoration: underline;
        }

        /* =========================================
           BADGES
        ========================================= */

        .dh-badge {
            display: inline-block;
            border-radius: 20px;
            background: var(--dh-green-light);
            color: var(--dh-green-dark);
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 700;
        }

        /* =========================================
           ACTIVITY ITEMS
        ========================================= */

        .dh-activity-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #f0f2f0;
        }

        .dh-activity-item:last-child {
            border-bottom: 0;
        }

        .dh-activity-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--dh-green-light);
            color: var(--dh-green);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dh-mobile-menu {
            display: none;
        }

        /* =========================================
           RESPONSIVE NAVBAR AND SIDEBAR
        ========================================= */

        @media (max-width: 1100px) {
            .dh-sidebar {
                width: 220px;
            }

            .dh-main {
                margin-left: 220px;
            }

            .dh-brand {
                min-width: auto;
            }

            .dh-search {
                margin: 0 18px;
            }
        }

        @media (max-width: 768px) {
            .dh-navbar {
                padding: 0 14px;
                height: 60px;
            }

            .dh-brand-name {
                font-size: 16px;
            }

            .dh-brand-icon {
                width: 36px;
                height: 36px;
                font-size: 19px;
            }

            .dh-brand-subtitle {
                display: none;
            }

            .dh-search {
                display: none;
            }

            .dh-navbar-actions {
                gap: 5px;
            }

            .dh-main {
                margin-left: 0;
                padding: 78px 15px 25px;
            }

            .dh-mobile-menu {
                display: inline-flex;
            }

            .dh-page-title {
                font-size: 22px;
            }

            .dh-user-name {
                display: none;
            }

            /* Mobile sidebar drawer */
            .dh-sidebar {
                display: block;
                width: 270px;
                position: fixed;
                top: 60px;
                bottom: 0;
                left: 0;
                z-index: 1040;
                transform: translateX(-100%);
                transition: transform .25s ease;
                background: var(--dh-bg);
            }

            .dh-sidebar.open {
                transform: translateX(0);
            }

            /* Mobile sidebar overlay */
            .dh-sidebar-overlay.show {
                display: block;
                position: fixed;
                inset: 60px 0 0 0;
                background: rgba(0, 0, 0, .35);
                z-index: 1035;
            }
        }

        @media (max-width: 480px) {
            .dh-navbar {
                padding: 0 10px;
            }

            .dh-brand {
                gap: 7px;
            }

            .dh-brand-name {
                font-size: 15px;
            }

            .dh-navbar-actions .dh-icon-button {
                width: 35px;
                height: 35px;
            }

            .dh-main {
                padding-left: 12px;
                padding-right: 12px;
            }
        }

        /* Sidebar overlay - hidden by default */
        .dh-sidebar-overlay {
            display: none;
        }

        /* =========================================
           WEED SPECIES EXPLORER
        ========================================= */

        /* Species search container */
        .dh-species-search {
            display: flex;
            align-items: stretch;
            position: relative;
            gap: 10px;
            width: 100%;
        }

        /* Support search icon in older search layout */
        .dh-species-search > i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dh-muted);
            font-size: 16px;
            pointer-events: none;
        }

        /* Search field wrapper (new layout) */
        .dh-search-field {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .dh-search-field .dh-search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dh-muted);
            font-size: 16px;
            pointer-events: none;
        }

        .dh-species-search input,
        .dh-search-field input {
            flex: 1;
            min-width: 0;
            width: 100%;
            height: 44px;
            background: #f7f8f7;
            border: 1px solid var(--dh-border);
            border-radius: 10px;
            padding: 10px 42px;
            outline: none;
            box-sizing: border-box;
        }

        .dh-species-search input:focus,
        .dh-search-field input:focus {
            background: #fff;
            border-color: #8ba883;
            box-shadow: 0 0 0 3px rgba(57, 91, 50, .08);
        }

        /* Search clear button */
        .dh-search-clear {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 17px;
            line-height: 1;
        }

        .dh-search-clear:hover {
            color: #dc3545;
        }

        /* Search button */
        .dh-search-button,
        .dh-species-search button {
            flex-shrink: 0;
            min-width: 105px;
            height: 44px;
        }

        /* =========================================
           CLASSIFICATION FILTER CHIPS
        ========================================= */

        .dh-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border: 1px solid var(--dh-border);
            background: white;
            color: #4b5563;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 500;
            transition: .15s ease;
        }

        .dh-filter-chip:hover {
            background: #f1f5ef;
            border-color: #c8d6c3;
            color: var(--dh-green-dark);
        }

        .dh-filter-chip.active {
            background: var(--dh-green);
            color: white;
            border-color: var(--dh-green);
        }

        .dh-filter-chip.active .dh-filter-count {
            background: rgba(255,255,255,.2);
            color: white;
        }

        .dh-filter-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 20px;
            background: #f0f2f0;
            color: #6b7280;
            font-size: 11px;
        }

        /* =========================================
           SPECIES CARD GRID
        ========================================= */

        .dh-species-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-width: 0;
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: #fff;
            border: 1px solid var(--dh-border);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(0,0,0,.025);
        }

        .dh-species-card:hover {
            box-shadow: 0 5px 18px rgba(0,0,0,.07);
        }

        /* =========================================
           SPECIES IMAGE - FIXED FRAME
        ========================================= */

        .dh-species-card-image {
            display: block;
            position: relative;
            flex: 0 0 220px;
            width: 100%;
            height: 220px;
            min-height: 220px;
            max-height: 220px;
            overflow: hidden;
            background: #f1f4ed;
            box-sizing: border-box;
        }

        .dh-species-card-image img {
            display: block;
            position: static;
            width: 100%;
            height: 100%;
            min-width: 0;
            min-height: 0;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            object-position: center center;
            margin: 0;
            padding: 0;
            border: 0;
            transform: none;
            transition: none;
            box-sizing: border-box;
        }

        /* Image placeholder */
        .dh-species-no-image {
            display: flex;
            flex: 0 0 220px;
            width: 100%;
            height: 220px;
            min-height: 220px;
            max-height: 220px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
            overflow: hidden;
            background: #f1f4ed;
            color: #91a58b;
            box-sizing: border-box;
        }

        .dh-species-no-image i {
            font-size: 42px;
        }

        .dh-species-no-image span {
            font-size: 12px;
        }

        /* Classification badge */
        .dh-species-card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(255,255,255,.95);
            color: var(--dh-green-dark);
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            z-index: 2;
        }

        /* =========================================
           SPECIES CARD BODY
        ========================================= */

        .dh-species-card-body {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-width: 0;
            padding: 17px;
            overflow-wrap: anywhere;
        }

        .dh-species-card-title {
            color: var(--dh-green-dark);
            font-size: 17px;
            font-weight: 700;
            font-style: italic;
            line-height: 1.4;
            margin-bottom: 3px;
            overflow-wrap: anywhere;
        }

        .dh-species-author {
            color: var(--dh-muted);
            font-size: 12px;
            margin-bottom: 12px;
        }

        .dh-species-common-names {
            min-height: 48px;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 13px;
        }

        .dh-species-card-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .dh-species-card-meta i {
            color: #84977f;
            width: 16px;
            flex-shrink: 0;
        }

        .dh-species-card-footer {
            border-top: 1px solid #f0f2f0;
            padding-top: 13px;
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .dh-species-view-link {
            font-size: 12px;
            color: var(--dh-green);
            font-weight: 700;
        }

        .dh-species-view-link:hover {
            color: var(--dh-green-dark);
        }

        /* Empty search state */
        .dh-empty-icon {
            width: 70px;
            height: 70px;
            margin: auto;
            border-radius: 50%;
            background: var(--dh-green-light);
            color: var(--dh-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        /* =========================================
           SPECIES EXPLORER RESPONSIVE
        ========================================= */

        @media (max-width: 576px) {
            /* Keep the search field and button vertically stacked */
            .dh-species-search {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .dh-search-field {
                width: 100%;
                flex: none;
            }

            .dh-species-search input,
            .dh-search-field input {
                width: 100%;
            }

            .dh-search-button,
            .dh-species-search button {
                width: 100%;
                min-width: 0;
            }

            .dh-filter-chip {
                font-size: 12px;
                padding: 7px 10px;
            }

            /* Keep images the same size on mobile too */
            .dh-species-card-image,
            .dh-species-no-image {
                height: 220px;
                min-height: 220px;
                max-height: 220px;
                flex-basis: 220px;
            }

            /* =========================================
   WEED SPECIES PROFILE
========================================= */

.dh-profile-back {
    display: inline-flex;
    align-items: center;
    color: var(--dh-green);
    font-weight: 600;
    font-size: 13px;
}

.dh-profile-back:hover {
    color: var(--dh-green-dark);
}

.dh-profile-header {
    overflow: hidden;
}

.dh-profile-scientific-name {
    color: var(--dh-green-dark);
    font-size: 30px;
    font-weight: 700;
    font-style: italic;
    line-height: 1.35;
    overflow-wrap: anywhere;
    margin-bottom: 5px;
}

.dh-profile-author {
    color: var(--dh-muted);
    font-size: 14px;
}

.dh-profile-code {
    display: inline-flex;
    align-items: center;
    background: #f0f2f5;
    border: 1px solid var(--dh-border);
    border-radius: 20px;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 600;
    color: #526052;
}

.dh-profile-header-icon {
    width: 70px;
    height: 70px;
    flex-shrink: 0;
    border-radius: 18px;
    background: var(--dh-green-light);
    color: var(--dh-green);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}

.dh-profile-section-heading {
    display: flex;
    align-items: center;
    font-size: 16px;
    font-weight: 700;
    color: var(--dh-green-dark);
    padding-bottom: 14px;
    margin-bottom: 16px;
    border-bottom: 1px solid #f0f2f0;
}

.dh-profile-section-heading i {
    color: var(--dh-green);
    font-size: 18px;
}

/* Main species photo */

.dh-profile-main-image {
    width: 100%;
    height: 380px;
    background: #f1f4ed;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dh-profile-main-image img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
    object-position: center;
}

/* Photo gallery */

.dh-profile-gallery {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.dh-profile-gallery-item {
    min-width: 0;
    overflow: hidden;
    background: #f1f4ed;
    border-radius: 10px;
}

.dh-profile-gallery-item img {
    display: block;
    width: 100%;
    height: 130px;
    object-fit: contain;
    background: #f1f4ed;
}

.dh-profile-gallery-caption {
    font-size: 11px;
    color: var(--dh-muted);
    padding: 8px;
    overflow-wrap: anywhere;
}

/* No photo placeholder */

.dh-profile-no-photo {
    height: 250px;
    background: #f1f4ed;
    color: #91a58b;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
    text-align: center;
    padding: 20px;
}

.dh-profile-no-photo i {
    font-size: 45px;
}

/* Text content */

.dh-profile-content {
    color: #374437;
    font-size: 14px;
    line-height: 1.85;
    overflow-wrap: anywhere;
    white-space: normal;
}

.dh-profile-content p:last-child {
    margin-bottom: 0;
}

/* Taxonomy and ecology information */

.dh-profile-detail-list {
    display: flex;
    flex-direction: column;
}

.dh-profile-detail-row {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f2f0;
}

.dh-profile-detail-row:first-child {
    padding-top: 0;
}

.dh-profile-detail-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.dh-profile-detail-label {
    color: var(--dh-muted);
    font-size: 12px;
}

.dh-profile-detail-value {
    color: #293829;
    font-size: 14px;
    font-weight: 600;
    overflow-wrap: anywhere;
}

/* Common and local name tags */

.dh-profile-name-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.dh-profile-name-tag {
    display: inline-flex;
    align-items: center;
    background: var(--dh-green-light);
    color: var(--dh-green-dark);
    border: 1px solid #dce8d5;
    border-radius: 20px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 600;
    overflow-wrap: anywhere;
    max-width: 100%;
}

.dh-profile-source {
    background: #f7f8f7;
    border-radius: 10px;
    padding: 12px;
}

/* Profile responsive layout */

@media (max-width: 768px) {

    .dh-profile-scientific-name {
        font-size: 24px;
    }

    .dh-profile-header-icon {
        width: 52px;
        height: 52px;
        font-size: 25px;
        border-radius: 14px;
    }

    .dh-profile-main-image {
        height: 300px;
    }

    .dh-profile-gallery {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dh-profile-gallery-item img {
        height: 120px;
    }
}

@media (max-width: 480px) {

    .dh-profile-scientific-name {
        font-size: 22px;
    }

    .dh-profile-main-image {
        height: 260px;
    }

    .dh-profile-section-heading {
        font-size: 15px;
    }

    .dh-profile-gallery {
        gap: 8px;
    }
}

/* =========================================
   SPECIES PHOTO LIGHTBOX - DIALOG VERSION
========================================= */

.dh-lightbox-trigger {
    position: relative;
    display: block;
    width: 100%;
    padding: 0;
    border: none;
    background: transparent;
    cursor: zoom-in;
    overflow: hidden;
}

.dh-lightbox-trigger img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.dh-lightbox-main-trigger {
    height: 100%;
    min-height: 280px;
}

.dh-lightbox-main-trigger img {
    max-height: 520px;
}

.dh-profile-gallery-item .dh-lightbox-trigger {
    height: 150px;
    background: #f5f6f5;
    border-radius: 9px;
}

.dh-image-zoom-hint {
    position: absolute;
    right: 10px;
    bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 11px;
    color: white;
    background: rgba(0, 0, 0, 0.65);
    border-radius: 20px;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.dh-lightbox-trigger:hover .dh-image-zoom-hint,
.dh-lightbox-trigger:focus-visible .dh-image-zoom-hint {
    opacity: 1;
}

/* Full-screen native dialog */

.dh-photo-lightbox {
    position: fixed;
    inset: 0;
    width: 100%;
    max-width: none;
    height: 100%;
    max-height: none;
    margin: 0;
    padding: 55px 25px 30px;
    border: none;
    background: #111;
    color: white;
    overflow: hidden;
}

.dh-photo-lightbox[open] {
    display: flex;
    align-items: center;
    justify-content: center;
}

.dh-photo-lightbox::backdrop {
    background: rgba(0, 0, 0, 0.94);
}

.dh-lightbox-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    gap: 15px;
}

.dh-lightbox-content img {
    display: block;
    max-width: 100%;
    max-height: calc(100vh - 130px);
    width: auto;
    height: auto;
    object-fit: contain;
}

.dh-lightbox-caption {
    color: white;
    font-size: 14px;
    text-align: center;
}

.dh-lightbox-close {
    position: absolute;
    top: 20px;
    right: 25px;
    z-index: 2001;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    color: white;
    background: rgba(255, 255, 255, 0.15);
    border: none;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
}

.dh-lightbox-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

@media (max-width: 576px) {

    .dh-photo-lightbox {
        padding: 60px 12px 25px;
    }

    .dh-lightbox-content img {
        max-height: calc(100vh - 120px);
    }

    .dh-lightbox-close {
        top: 12px;
        right: 12px;
    }

    .dh-image-zoom-hint {
        opacity: 1;
    }

}

    </style>
</head>

<body>

    @include('partials.navbar')

    @auth
        @include('partials.sidebar')
    @endauth

    @auth
        <div class="dh-sidebar-overlay" id="sidebarOverlay"></div>
    @endauth

    <main class="dh-main @guest dh-guest-main @endguest">
        <div class="dh-page-container">
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="status">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const menuButton = document.getElementById('mobileMenuButton');
            const sidebar = document.getElementById('dhSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (!menuButton || !sidebar || !overlay) {
                return;
            }

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('show');
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            }

            menuButton.addEventListener('click', function () {
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            overlay.addEventListener('click', closeSidebar);

            sidebar.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    closeSidebar();
                });
            });

        });
    </script>

</body>
</html>
