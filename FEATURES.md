# KFA Highlight - Features Documentation

## Fitur yang Sudah Dibuat

### 1. PDF Upload Management
- **Path**: `/uploads`
- **Fitur**:
  - Upload PDF file (max 10MB)
  - Automatic PDF to image conversion (jika Imagick tersedia)
  - Edit judul, order, dan status aktif/tidak aktif
  - Delete PDF dengan konfirmasi
  - View PDF secara langsung

### 2. Welcome Page dengan Slideshow
- **Path**: `/`
- **Fitur**:
  - Image slideshow otomatis dari PDF yang di-upload
  - Manual navigation (prev/next buttons)
  - Indicator dots untuk navigasi
  - Auto-advance setiap 5 detik
  - Responsive design
  - Link ke PDF file untuk dibuka

### 3. User Management
- **Path**: `/users`
- **Fitur**:
  - CRUD user (Create, Read, Update, Delete)
  - List users dengan pagination
  - Edit user (name, email, password)
  - Delete user dengan proteksi (tidak bisa delete diri sendiri)
  - Password confirmation saat create/update

### 4. Dashboard
- **Path**: `/dashboard`
- **Fitur**:
  - Statistics cards:
    - Total uploads
    - Active uploads
  - Quick actions:
    - Upload PDF
    - Manage Users
  - Navigation ke upload management dan user management

### 5. Layout Changes
- **Perubahan**: Sidebar diganti dengan Navbar (Header)
- Navbar menampilkan navigasi di bagian atas
- Responsive design untuk mobile dan desktop

## Database Schema

### Table: pdf_uploads
- `id` - Primary key
- `user_id` - Foreign key ke users table
- `title` - Judul PDF
- `original_filename` - Nama file asli
- `pdf_path` - Path ke file PDF
- `image_path` - Path ke image preview (nullable)
- `order` - Urutan tampilan (default: 0)
- `is_active` - Status aktif/tidak (default: true)
- `created_at` - Timestamp
- `updated_at` - Timestamp

## Routes

### Public Routes
- `GET /` - Welcome page dengan slideshow

### Authenticated Routes
- `GET /dashboard` - Dashboard dengan statistics
- Resource routes untuk uploads:
  - `GET /uploads` - List uploads
  - `GET /uploads/create` - Form upload
  - `POST /uploads` - Store upload
  - `GET /uploads/{id}/edit` - Form edit
  - `PUT /uploads/{id}` - Update upload
  - `DELETE /uploads/{id}` - Delete upload
- Resource routes untuk users:
  - `GET /users` - List users
  - `GET /users/create` - Form create user
  - `POST /users` - Store user
  - `GET /users/{id}/edit` - Form edit user
  - `PUT /users/{id}` - Update user
  - `DELETE /users/{id}` - Delete user

## Tech Stack
- **Backend**: Laravel 12
- **Frontend**: React 19 + TypeScript
- **Styling**: Tailwind CSS 4
- **UI Components**: shadcn/ui
- **State Management**: Inertia.js
- **PDF Processing**: Imagick (optional, untuk generate preview image)

## Setup Instructions

1. Install dependencies:
```bash
composer install
npm install
```

2. Setup environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Run migrations:
```bash
php artisan migrate
```

4. Create storage link:
```bash
php artisan storage:link
```

5. Build assets:
```bash
npm run build
# or for development
npm run dev
```

6. Optional: Install Imagick untuk PDF preview
```bash
# Windows: Download dari https://windows.php.net/downloads/pecl/releases/imagick/
# Linux: sudo apt-get install php-imagick
# Mac: brew install imagemagick
```

## Usage

1. Login ke aplikasi
2. Buka Dashboard untuk melihat overview
3. Upload PDF melalui "Upload PDF" button
4. Manage users melalui "Manage Users"
5. PDF yang di-upload akan muncul di homepage (welcome page) dalam bentuk slideshow
6. Set `is_active` ke false untuk hide PDF dari homepage

## Notes

- PDF preview (image_path) akan generate otomatis jika Imagick extension tersedia
- Jika Imagick tidak tersedia, slideshow akan menampilkan placeholder
- File PDF disimpan di `storage/app/public/pdfs`
- Image preview disimpan di `storage/app/public/pdf-images`
