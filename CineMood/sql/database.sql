
-- Deneme Kodları

select * from users ORDER BY created_at DESC


select * from watch_later ORDER BY created_at DESC


select * from watched ORDER BY watched_at DESC


select * from favorites ORDER BY created_at DESC



+---------------+       +---------------+
|    users      |       |   watched     |
|---------------|       |---------------|
| id (PK)       |<--+--| id (PK)       |
| first_name    |   |  | user_id (FK)  |
| last_name     |   |  | content_id    |
| email         |   |  | content_type  |
| phone         |   |  | title         |
| birth_date    |   |  | poster_path   |
| password      |   |  | watched_at    |
| created_at    |   |  +---------------+
| updated_at    |   |
+---------------+   |
                    |
                    |  +---------------+
                    |  |   favorites   |
                    |  |---------------|
                    +--| id (PK)       |
                    |  | user_id (FK)  |
                    |  | content_id    |
                    |  | content_type  |
                    |  | title         |
                    |  | poster_path   |
                    |  | created_at    |
                    |  +---------------+
                    |
                    |  +---------------+
                    |  |  watch_later  |
                    |  |---------------|
                    +--| id (PK)       |
                       | user_id (FK)  |
                       | content_id    |
                       | content_type  |
                       | title         |
                       | poster_path   |
                       | created_at    |
                       +---------------+