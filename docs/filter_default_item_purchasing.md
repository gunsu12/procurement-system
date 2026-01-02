# Dokumentasi: Filter Default Item Purchasing

## Perubahan yang Dibuat

### 1. Database Migration
**File**: `database/migrations/2026_01_02_083420_add_default_item_purchasing_to_users_table.php`

Menambahkan kolom baru di tabel `users`:
- **Kolom**: `default_item_purchasing`
- **Tipe**: ENUM('medis', 'non medis')
- **Nullable**: Ya
- **Posisi**: Setelah kolom `role`
- **Komentar**: Default filter for item purchasing: medis or non medis

### 2. Model User
**File**: `app/Models/User.php`

Menambahkan `default_item_purchasing` ke dalam array `$fillable` agar kolom dapat diisi secara mass assignment.

### 3. Controller
**File**: `app/Http/Controllers/ProcurementController.php`

**Method**: `index()`

Menambahkan 2 fitur:

#### 3.1 Auto-Filter Berdasarkan User Preference
Pada baris 57-62, ditambahkan logika:
```php
// Auto-filter by is_medical for purchasing users based on their default preference
// Only apply if user has default_item_purchasing set and is_medical filter is not manually selected
if ($user->role === 'purchasing' && $user->default_item_purchasing && !$request->has('is_medical')) {
    $isMedical = ($user->default_item_purchasing === 'medis') ? 1 : 0;
    $request->merge(['is_medical' => $isMedical]);
}
```

**Cara Kerja**:
- Cek apakah user memiliki role `purchasing`
- Cek apakah user memiliki preferensi `default_item_purchasing` yang sudah di-set
- Cek apakah user TIDAK memberikan filter `is_medical` secara manual
- Jika semua kondisi terpenuhi, otomatis set filter `is_medical` sesuai preferensi user

#### 3.2 Manual Filter
Pada baris 75-78, ditambahkan logika:
```php
// Filter by is_medical (medis/non medis)
if ($request->has('is_medical') && $request->is_medical !== '') {
    $query->where('is_medical', $request->is_medical);
}
```

**Cara Kerja**:
- Jika request memiliki parameter `is_medical` dan nilainya tidak kosong
- Filter query berdasarkan nilai `is_medical`

### 4. View
**File**: `resources/views/procurement/index.blade.php`

Menambahkan dropdown filter baru di antara filter "Status" dan "Start Date":

```blade
<div class="col-md-2 mb-2">
    <label>Type</label>
    <select name="is_medical" class="form-control">
        <option value="">All Types</option>
        <option value="1" {{ request('is_medical') == '1' ? 'selected' : '' }}>Medis</option>
        <option value="0" {{ request('is_medical') == '0' ? 'selected' : '' }}>Non Medis</option>
    </select>
</div>
```

## Cara Menggunakan

### 1. Set Default Preference User
Update user di database untuk mengatur preferensi default:
```sql
UPDATE users 
SET default_item_purchasing = 'medis' 
WHERE id = 1 AND role = 'purchasing';
```

Atau
```sql
UPDATE users 
SET default_item_purchasing = 'non medis' 
WHERE id = 2 AND role = 'purchasing';
```

### 2. Akses Menu Request
Ketika user dengan role `purchasing` dan memiliki `default_item_purchasing` yang sudah di-set mengakses `/procurement`:
- Sistem otomatis akan menampilkan data sesuai filter defaultnya
- User masih bisa mengubah filter secara manual melalui dropdown "Type"

### 3. Filter Manual
Semua user (tidak hanya purchasing) dapat menggunakan dropdown "Type" untuk filter:
- **All Types**: Menampilkan semua data (medis dan non medis)
- **Medis**: Menampilkan hanya data medis
- **Non Medis**: Menampilkan hanya data non medis

## Catatan Penting

1. **Auto-filter hanya berlaku untuk role `purchasing`**
   - User dengan role lain tidak akan mendapat auto-filter
   - Mereka tetap bisa menggunakan filter manual

2. **Manual filter prioritas lebih tinggi**
   - Jika user memilih filter manual, auto-filter tidak akan diterapkan
   - User bisa override preferensi default mereka kapan saja

3. **Kolom `default_item_purchasing` nullable**
   - User tidak wajib memiliki preferensi default
   - Jika NULL, tidak ada auto-filter yang diterapkan

## Migrasi Data Existing Users

Jika ingin mengatur default preference untuk semua purchasing users yang sudah ada:

```sql
-- Contoh: Set semua purchasing user ke 'medis'
UPDATE users 
SET default_item_purchasing = 'medis' 
WHERE role = 'purchasing' 
AND default_item_purchasing IS NULL;
```

Atau bisa dibuat seeder untuk data yang lebih kompleks.
