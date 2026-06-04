# Lakhan Public School — Server Deploy Guide

## Quick answer: DB dubara poori import?

| Server situation | What to do |
|------------------|------------|
| **Nayi hosting / khali database** | Option A ya B (neeche) |
| **Purani live DB + data rakna hai** | **Option B ya C only** — poora `lps_db.sql` mat import karo |

---

## Option A — Auto setup (sabse aasaan)

1. `config/db.php` mein live database name, user, password set karo.
2. Poori website FTP/cPanel se upload karo.
3. Browser mein kholo:
   ```
   https://lakhanpublicschool.com/server_setup.php?key=lps_setup_2026
   ```
   Pehle `server_setup.php` file mein `$SETUP_KEY` apna secret rakho.
4. Page par sab green ✓ aaye to:
   - Website kholo
   - Admin login karo
   - **`server_setup.php` delete kar do**
5. Agar admin user nahi hai to URL mein add karo:
   ```
   ?key=YOUR_SECRET&create_admin=1
   ```
   (Login: `admin` / `admin123` — turant password change karo)

---

## Option B — phpMyAdmin SQL (manual)

### Fresh database (kuch data nahi hai)
- Import: **`db/lps_db.sql`** (full database + sample data)

### Purani database update (data safe)
- Import: **`db/server_migrate_only.sql`**
- Sirf nayi tables / columns add honge, purana data delete nahi hoga

---

## Option C — Sirf nayi tables (copy-paste)

phpMyAdmin → SQL tab → yeh chalao:

```sql
CREATE TABLE IF NOT EXISTS hero_slides (
  id INT AUTO_INCREMENT PRIMARY KEY,
  image_filename VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Settings keys admin panel se auto bhi ban sakti hain jab **Global Settings** khologe.

---

## Files jo server par zaroor hon

| Folder / file | Purpose |
|---------------|---------|
| `config/db.php` | Database connection (live credentials) |
| `images/gallery/` | Gallery photos |
| `images/main-slider/` | Hero slider images |
| `images/site/` | Uploaded logo |
| `images/popup/` | Popup image |
| `images/results/` | Result posters |
| `uploads/announcements/` | Announcement attachments |

---

## Deploy ke baad delete karo (security)

- `server_setup.php`
- `admin/reset_admin.php`
- `setup_db.php` (agar upload ho gaya ho)

---

## Tables list (poori website ke liye)

| Table | Use |
|-------|-----|
| `admin_users` | Admin login |
| `enquiries` | Contact form messages |
| `gallery` | School gallery |
| `hero_slides` | Homepage hero slider |
| `site_settings` | Contact, social, logo, features, rules |
| `announcements` | Announcements |
| `student_results` | Outstanding results |

---

## Help

- DB connection error → `config/db.php` credentials check karo  
- Admin login fail → `admin/fix_password.php?key=lps_setup_2026` (set bcrypt hash, then delete file)  
- Hero / logo nahi dikhe → admin **Global Settings** se upload karo + images folder permissions `755`
