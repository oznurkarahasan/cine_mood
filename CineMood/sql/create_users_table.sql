-- Users tablosunu oluştur
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(10) NOT NULL UNIQUE,
    birth_date DATE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Email ve telefon numarası için indeksler
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_phone ON users(phone);

-- Güncelleme zamanını otomatik güncelleyen trigger
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Açıklama ekle
COMMENT ON TABLE users IS 'Kullanıcı bilgilerini içeren tablo';
COMMENT ON COLUMN users.id IS 'Otomatik artan benzersiz kullanıcı ID';
COMMENT ON COLUMN users.first_name IS 'Kullanıcının adı';
COMMENT ON COLUMN users.last_name IS 'Kullanıcının soyadı';
COMMENT ON COLUMN users.email IS 'Kullanıcının e-posta adresi (benzersiz)';
COMMENT ON COLUMN users.phone IS 'Kullanıcının telefon numarası (benzersiz)';
COMMENT ON COLUMN users.birth_date IS 'Kullanıcının doğum tarihi';
COMMENT ON COLUMN users.password IS 'Hashlenmiş şifre';
COMMENT ON COLUMN users.created_at IS 'Hesabın oluşturulma tarihi';
COMMENT ON COLUMN users.updated_at IS 'Son güncelleme tarihi'; 

--avatar ekleme
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT 'blank-profile-picture-973460_1280.webp';