# Manager Policy Updates - Summary

**Updated:** 2026-01-29

---

## 📋 Overview

Policy untuk role **Manager** telah diupdate agar manager hanya bisa melihat dan mengelola procurement request dari unit dimana mereka ditetapkan sebagai **approver** (melalui field `approval_by` di tabel `units`).

**🔥 PENTING: Manager bisa lihat dan approve request dari company manapun, selama dia adalah approver dari unit tersebut.**

---

## ✅ Yang BISA Dilakukan Manager

### 1. **View (Lihat Request)**
- Manager **hanya bisa melihat** request dari unit dimana dia sebagai approver
- **DARI COMPANY MANAPUN** (tidak dibatasi company)
- Dicek melalui: `unit.approval_by === user.id`

**Policy (`ProcurementPolicy::view`):**
```php
// Manager can view requests from any company as long as they are the unit approver
if ($user->role === 'manager') {
    $unit = $procurement->unit;
    return $unit && $unit->approval_by === $user->id;
}
```

### 2. **Approve (Menyetujui Request)**
- Manager hanya bisa approve request dengan status `submitted` → `approved_by_manager`
- Harus dari unit dimana dia sebagai approver
- **BISA DARI COMPANY MANAPUN**

**Policy (`ProcurementPolicy::approve`):**
```php
// Manager can approve requests from any company as long as they are the approver
if ($user->role === 'manager') {
    $unit = $procurement->unit;
    if (!$unit || $unit->approval_by !== $user->id) {
        return false; // Tidak bisa approve
    }
}
```

### 3. **Reject (Menolak Request)**
- Manager bisa menolak seluruh request
- Tetap bisa, selama dari unit dimana dia approver

### 4. **Reject Item (Menolak Item Tertentu)**
- Manager bisa menolak item tertentu dalam request (bukan seluruh request)
- Hanya untuk request dengan status `submitted`
- Harus dari unit dimana dia sebagai approver

**Policy (`ProcurementPolicy::rejectItem`):**
```php
if ($user->role === 'manager') {
    $unit = $procurement->unit;
    if (!$unit || $unit->approval_by !== $user->id) {
        return false;
    }
}
```

### 5. **Cancel Reject Item**
- Manager bisa membatalkan penolakan item
- Sama seperti reject item

---

## ❌ Yang TIDAK BISA Dilakukan Manager

1. **Melihat request dari unit lain** (kecuali dia approver unit tersebut)
2. **Melihat request jika bukan approver dari unit**
3. **Edit request** (hanya owner/creator yang bisa)
4. **Delete documents** (hanya owner yang bisa)

**❌ REMOVED:** ~~Melihat request dari company lain~~ → Manager **SEKARANG BISA** lihat dari company lain selama dia approver

---

## 🔧 Implementation Details

### Files Modified

#### 1. **ProcurementPolicy.php**
- `view()` - Updated to check `approval_by`
- `approve()` - Updated to check `approval_by`
- `rejectItem()` - Updated to check `approval_by`

#### 2. **ProcurementController.php**
**Filter Logic (line 31-45):**
```php
// Manager only sees requests from units where they are the approver
if ($user->role === 'manager') {
    $query->whereHas('unit', function ($q) use ($user) {
        $q->where('approval_by', $user->id);
    });
}
```

#### 3. **HomeController.php**
**Dashboard Stats (line 29-37):**
```php
// Manager only sees stats from units where they are the approver
if ($user->role === 'manager') {
    $query->whereHas('unit', function ($q) use ($user) {
        $q->where('approval_by', $user->id);
    });
}
```

#### 4. **ReportController.php**
Updated di 3 report methods:
- `unit()` - Unit Report
- `outstanding()` - Outstanding Report
- `timeline()` - Timeline Report

Semua menggunakan filter yang sama:
```php
if ($user->role === 'manager') {
    $query->whereHas('unit', function ($q) use ($user) {
        $q->where('approval_by', $user->id);
    });
}
```

#### 5. **show.blade.php (Procurement Detail View)**
Updated conditional rendering untuk:
- Line 256: Table header untuk action buttons
- Line 311: Reject item buttons
- Line 402-414: `$canApprove` logic

Changed from:
```php
$procurement->unit_id == Auth::user()->unit_id
```

To:
```php
$procurement->unit->approval_by == Auth::user()->id
```

#### 6. **index.blade.php (Procurement List View)** 🆕
Added JavaScript to clean empty query parameters:
```javascript
// Remove empty query parameters before form submission
$('form[action="{{ route('procurement.index') }}"]').on('submit', function(e) {
    $(this).find('input, select, textarea').each(function() {
        var $input = $(this);
        var value = $input.val();
        
        // Disable (remove) input if value is empty
        if (value === '' || value === null) {
            $input.prop('disabled', true);
        }
    });
});
```

**Why?** Prevents URL pollution with empty parameters like `?status=approved_by_manager&is_medical=&start_date=&end_date=`

---

## 📊 Contoh Skenario

### Scenario 1: Manager A - Approver Multiple Units (Cross-Company)

**Setup:**
- Manager A di-assign sebagai approver untuk:
  - Unit IT di **Company BROS** (id: 1)
  - Unit Finance di **Company BIRO** (id: 2) ← **Beda company!**
- Manager A **BUKAN** approver untuk:
  - Unit HR di **Company BROS** (id: 3)

**Behaviour:**
- ✅ Manager A bisa lihat & approve request dari Unit IT (Company BROS)
- ✅ Manager A bisa lihat & approve request dari Unit Finance (**Company BIRO** - cross-company!)
- ❌ Manager A **TIDAK BISA** lihat request dari Unit HR (bukan approver)

### Scenario 2: Manager B - Single Unit Approver

**Setup:**
- Manager B bekerja di **Company BROS**
- Manager B di-assign sebagai approver untuk:
  - Unit HR di **Company BROS** (id: 3)

**Behaviour:**
- ✅ Manager B bisa lihat & approve request dari Unit HR
- ❌ Manager B **TIDAK BISA** lihat request dari Unit IT atau Finance (bukan approver)

### Scenario 3: Manager C - Cross-Company Approver

**Setup:**
- Manager C bekerja di **Company BROS** (company_id = 1)
- Manager C di-assign sebagai approver untuk:
  - Unit Operations di **Company BIRO** (company_id = 2) ← **Beda company dari tempat dia bekerja!**

**Behaviour:**
- ✅ Manager C **BISA** lihat request dari Unit Operations (Company BIRO)
- ✅ Manager C **BISA** approve request dari Unit Operations (Company BIRO)
- ✅ **Cross-company access works!**

### Scenario 4: Manager Tidak Di-assign

**Setup:**
- Manager D **TIDAK** di-assign sebagai approver di unit manapun

**Behaviour:**
- ❌ Manager D **TIDAK BISA** lihat request apapun
- ❌ Manager D **TIDAK BISA** approve request apapun
- Dashboard akan kosong

---

## 🔍 Database Schema

### Table: `units`
```sql
approval_by (nullable, foreign key to users.id)
```

**Relationship:**
- `Unit` belongsTo `User` (approver)
- `User` hasMany `Unit` (approved units)

**Di Model Unit:**
```php
public function approver()
{
    return $this->belongsTo(User::class, 'approval_by');
}
```

---

## 🎯 Default Filter

**ProcurementController saat Manager login:**
- Default status filter: `submitted`
- Default unit filter: Units where `approval_by = manager.id`

Artinya, saat manager membuka procurement list, otomatis akan melihat:
- Request dengan status "submitted" (menunggu approval)
- Dari unit-unit dimana dia sebagai approver

---

## ⚠️ Important Notes

1. **Satu Unit, Satu Approver**: Saat ini, satu unit hanya bisa punya satu approver (`approval_by` is not an array)
2. **Nullable Field**: `approval_by` nullable, jadi unit bisa tidak punya approver
3. **🔥 Cross-Company Access**: Manager **BISA** lihat data dari company lain, **SELAMA** dia adalah approver dari unit tersebut
4. **No Company Restriction for Manager**: Manager tidak dibatasi oleh `company_id`, hanya dibatasi oleh `approval_by`

---

## 🧪 Testing Checklist

- [ ] Manager bisa lihat request dari unit dimana dia approver
- [ ] Manager TIDAK bisa lihat request dari unit lain
- [ ] Manager bisa approve request dari unit dimana dia approver
- [ ] Manager TIDAK bisa approve request dari unit lain
- [ ] Manager bisa reject/reject item dari unit dimana dia approver
- [ ] Dashboard menampilkan stats yang benar untuk manager
- [ ] Reports (unit, outstanding, timeline) menampilkan data yang benar
- [ ] Manager tanpa unit assignment tidak bisa lihat data apapun
- [ ] Policy `view()`, `approve()`, `rejectItem()` berfungsi dengan benar

---

## 📝 Migration Notes

**Existing Data:**
- Unit yang sudah ada mungkin belum punya `approval_by`
- Perlu di-update manually melalui UI master data unit
- Manager tidak akan bisa lihat request dari unit tanpa approver

**Recommended Action:**
1. Setup approver untuk semua unit yang ada
2. Inform users tentang perubahan policy
3. Verify data access setelah migration
