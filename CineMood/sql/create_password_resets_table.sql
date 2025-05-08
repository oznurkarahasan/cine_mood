-- Password resets tablosunu oluştur
CREATE TABLE password_resets (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    verification_code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (email) REFERENCES users(email)
);

-- Comments tablosu
CREATE TABLE comments (
    comment_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    tmdb_id INT NOT NULL,
    type VARCHAR(10) NOT NULL CHECK (type IN ('movie', 'tv')),
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TRIGGER update_comments_updated_at
BEFORE UPDATE ON comments
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- İndeksler
CREATE INDEX idx_password_resets_email ON password_resets(email);
CREATE INDEX idx_password_resets_code ON password_resets(verification_code);

-- Açıklama ekle
COMMENT ON TABLE password_resets IS 'Şifre sıfırlama kodlarını içeren tablo';
COMMENT ON COLUMN password_resets.id IS 'Otomatik artan benzersiz ID';
COMMENT ON COLUMN password_resets.email IS 'Kullanıcının e-posta adresi';
COMMENT ON COLUMN password_resets.verification_code IS '6 haneli doğrulama kodu';
COMMENT ON COLUMN password_resets.created_at IS 'Kodun oluşturulma tarihi';
COMMENT ON COLUMN password_resets.used IS 'Kodun kullanılıp kullanılmadığı'; 