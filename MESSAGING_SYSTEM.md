# Messaging System

## Overview
Simple in-app messaging system for buyers and sellers to communicate about products.

## Features
- ✅ One-on-one conversations
- ✅ Product context in messages
- ✅ Unread message badges
- ✅ Real-time message display
- ✅ Mobile responsive design
- ✅ Message history

## Files Created
1. **database/messages.sql** - Database table schema
2. **messages.php** - Conversations list page
3. **chat.php** - Individual chat page
4. **import_messages.php** - Database import script

## Files Modified
1. **product-detail.php** - Added "Send Message" button
2. **includes/header.php** - Added Messages link with unread count
3. **includes/bottom-nav.php** - Added Messages to mobile navigation

## Installation
1. Run the import script:
   ```
   http://localhost/curuzamuhinzi/import_messages.php
   ```

2. The messages table will be created with:
   - sender_id, receiver_id (user references)
   - product_id (optional product context)
   - message (text content)
   - is_read (read status)
   - created_at (timestamp)

## Usage

### For Buyers
1. Browse products on feed
2. Click product to view details
3. Click "Send Message" button (must be logged in)
4. Type message and send
5. View all conversations in Messages page

### For Sellers
1. Receive messages from interested buyers
2. Unread count shows in header badge
3. Click Messages to view all conversations
4. Reply to buyer inquiries

### Message Flow
```
Product Detail → Send Message → Chat Page → Messages List
```

## Database Schema
```sql
messages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  sender_id INT (FK to users),
  receiver_id INT (FK to users),
  product_id INT (FK to products, nullable),
  message TEXT,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP
)
```

## Access Points
- **Desktop**: Header → Messages icon
- **Mobile**: Bottom nav → Messages tab
- **Product Page**: Send Message button (logged in users only)

## Security Notes
- Users can only message other users (not themselves)
- Messages are private between sender and receiver
- Product context is optional but helpful
- No message editing/deletion (keep it simple)

## Future Enhancements (Optional)
- Message notifications
- Image attachments
- Message search
- Block/report users
- Typing indicators
- Read receipts
