# Search & User Profiles

## Search & Filters (search.php)

### Features
✅ **Text Search** - Search by product name or description
✅ **Category Filter** - Filter by crops, seeds, livestock, equipment, tools
✅ **Location Filter** - Filter by province
✅ **Price Range** - Min/max price filtering
✅ **Sorting Options**:
  - Newest First
  - Price: Low to High
  - Price: High to Low
  - Most Popular (by views)

### Access Points
- Header search bar → Type & press Enter
- Direct URL: `/search.php`
- With filters: `/search.php?q=maize&category=seeds&province=Kigali&sort=price_low`

### Usage
1. Type search query in header
2. Apply filters in sidebar
3. Sort results by preference
4. Click product to view details

## User Profiles (user-profile.php)

### Features
✅ **Profile Header** - Avatar, name, role, location
✅ **Stats** - Total products, total views
✅ **Contact Actions**:
  - Send Message (in-app)
  - WhatsApp
  - Call
✅ **About Section** - Phone, email, location, member since
✅ **Products Grid** - All active products from seller

### Access Points
- Product detail page → "View Seller Profile" button
- Direct URL: `/user-profile.php?id=USER_ID`
- From messages → Click user avatar/name

### Profile Sections
1. **Banner** - Gradient background
2. **Avatar** - Profile picture or initial
3. **Info** - Name, role badge, location
4. **Stats** - Product count, view count
5. **Actions** - Message, WhatsApp, Call buttons
6. **About** - Contact details, location, join date
7. **Products** - Grid of all seller's products

## Files Created
1. **search.php** - Search & filter page
2. **user-profile.php** - User profile page
3. **SEARCH_PROFILES.md** - This documentation

## Files Modified
1. **product-detail.php** - Updated profile link
2. **includes/header.php** - Made search functional

## Database Queries
- Search uses LIKE queries on product_name and description
- Filters use WHERE clauses for category, province, price
- Sorting uses ORDER BY on price, created_at, views_count
- Profile loads user data + all their active products

## Responsive Design
- Desktop: Sidebar filters + grid layout
- Mobile: Stacked filters + 2-column grid
- Profile adapts to mobile with centered layout

## Future Enhancements (Optional)
- Autocomplete suggestions
- Search history
- Save searches
- User ratings/reviews
- Verified seller badges
- Follow/favorite sellers
