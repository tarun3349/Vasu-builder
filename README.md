# KTS Aquarium and Pets - Premium E-commerce Website

A comprehensive e-commerce website for aquarium and pet supplies with a full admin panel, built using HTML, CSS, JavaScript, PHP, and MySQL.

## Features

### Customer Features
- **User Registration & Login**: Secure authentication system with email and password
- **Product Browsing**: Browse products by categories with search functionality
- **Shopping Cart**: Add/remove products, update quantities
- **Order Placement**: Place orders via WhatsApp integration
- **Order Tracking**: View order history and status
- **Responsive Design**: Premium aquarium-themed UI that works on all devices

### Admin Features
- **User Management**: Create, view, and manage customer accounts
- **Product Management**: Add, edit, delete products with categories
- **Category Management**: Organize products into categories
- **Order Management**: Track and update order status
- **Settings**: Configure site settings and change admin password
- **Search & Filter**: Advanced search functionality for users, products, and orders

### WhatsApp Integration
- **Order Notifications**: Orders are automatically sent to admin via WhatsApp
- **Customer Communication**: Direct WhatsApp link for order confirmation

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional)

### Step 1: Database Setup
1. Create a MySQL database named `kts_aquarium`
2. Import the database schema:
   ```sql
   mysql -u root -p kts_aquarium < database.sql
   ```

### Step 2: Configuration
1. Update database credentials in `config/database.php`:
   ```php
   $host = 'localhost';
   $dbname = 'kts_aquarium';
   $username = 'your_username';
   $password = 'your_password';
   ```

2. Update WhatsApp number in `config/database.php`:
   ```php
   define('WHATSAPP_NUMBER', '+919597203715');
   ```

### Step 3: File Permissions
Ensure the following directories are writable:
- `config/`
- `assets/`

### Step 4: Web Server Configuration
1. Point your web server document root to the project directory
2. Ensure mod_rewrite is enabled (for Apache)
3. Set up virtual host (optional but recommended)

### Step 5: Access the Application
1. **Customer Access**: Visit `http://your-domain/`
2. **Admin Access**: 
   - Default admin credentials:
     - Email: `admin@ktsaquarium.com`
     - Password: `password`
   - Access admin panel at: `http://your-domain/admin/`

## File Structure

```
kts_aquarium/
├── admin/                  # Admin panel files
│   ├── dashboard.php      # Admin dashboard
│   ├── users.php         # User management
│   ├── products.php      # Product management
│   ├── categories.php    # Category management
│   ├── orders.php        # Order management
│   ├── order_details.php # Order details view
│   └── settings.php      # Admin settings
├── ajax/                 # AJAX handlers
│   └── add_to_cart.php   # Add to cart functionality
├── assets/               # Static assets
│   └── css/
│       └── style.css     # Main stylesheet
├── config/               # Configuration files
│   └── database.php      # Database configuration
├── includes/             # Common includes
│   └── functions.php     # Common functions
├── index.php             # Homepage
├── login.php             # User login
├── register.php          # User registration
├── logout.php            # Logout handler
├── dashboard.php         # Customer dashboard
├── products.php          # Product listing
├── product_details.php   # Product details page
├── categories.php        # Category listing
├── cart.php              # Shopping cart
├── checkout.php          # Checkout process
├── order_success.php     # Order success page
├── order_details.php     # Customer order details
├── database.sql          # Database schema
└── README.md             # This file
```

## Default Admin Account

- **Email**: admin@ktsaquarium.com
- **Password**: password
- **Mobile**: +919597203715

**Important**: Change the default admin password immediately after first login.

## Key Features Explained

### WhatsApp Integration
When customers place orders, the system automatically generates a WhatsApp message with:
- Order details
- Customer information
- Product list
- Total amount
- Shipping address

### User Management
- Admin can create new users (customers or admins)
- Search users by name, email, or mobile
- Delete users (except own account)
- View user order history

### Product Management
- Add products with categories, prices, descriptions
- Upload product images (URL-based)
- Manage stock quantities
- Enable/disable products
- Search and filter products

### Order Management
- View all orders with customer details
- Update order status (pending, confirmed, shipped, delivered, cancelled)
- Search orders by various criteria
- View detailed order information

### Security Features
- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- Input sanitization
- Session management
- Admin authentication

## Customization

### Styling
The website uses a premium aquarium theme with:
- Ocean blue color scheme
- Fish and aquarium emojis
- Gradient backgrounds
- Modern card-based layout
- Responsive design

### Adding New Features
1. **New Admin Pages**: Add files in `admin/` directory
2. **New Customer Pages**: Add files in root directory
3. **Database Changes**: Update `database.sql` and run migrations
4. **Styling**: Modify `assets/css/style.css`

## Troubleshooting

### Common Issues
1. **Database Connection Error**: Check credentials in `config/database.php`
2. **Permission Denied**: Ensure proper file permissions
3. **WhatsApp Not Working**: Verify WhatsApp number format
4. **Cart Not Working**: Check if user is logged in

### Debug Mode
Enable error reporting by adding to the top of PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Support

For support or questions:
- Email: admin@ktsaquarium.com
- WhatsApp: +919597203715
- Location: Salem, Tamil Nadu

## License

This project is proprietary software for KTS Aquarium and Pets.

---

**Note**: This is a complete e-commerce solution with all the requested features including admin panel, user management, product management, order management, and WhatsApp integration. The system is ready for production use with proper server configuration.