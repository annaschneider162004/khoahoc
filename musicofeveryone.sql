CREATE DATABASE IF NOT EXISTS musicofeveryone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE musicofeveryone;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS rankings;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS friendships;
DROP TABLE IF EXISTS community_likes;
DROP TABLE IF EXISTS community_comments;
DROP TABLE IF EXISTS community_posts;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS user_badges;
DROP TABLE IF EXISTS badges;
DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS assignments;
DROP TABLE IF EXISTS video_likes;
DROP TABLE IF EXISTS video_comments;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS livestreams;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student', 'teacher') NOT NULL DEFAULT 'student',
    avatar_path VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    points INT NOT NULL DEFAULT 0,
    locked TINYINT(1) NOT NULL DEFAULT 0,
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    cover_image VARCHAR(255) DEFAULT NULL,
    category VARCHAR(80) NOT NULL,
    teacher_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_courses_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT DEFAULT NULL,
    lesson_date DATETIME DEFAULT NULL,
    video_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    progress DECIMAL(5,2) NOT NULL DEFAULT 0,
    completed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_enrollment (user_id, course_id),
    CONSTRAINT fk_enrollments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) DEFAULT NULL,
    mime_type VARCHAR(120) NOT NULL,
    privacy ENUM('public', 'community', 'private') NOT NULL DEFAULT 'community',
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    views INT NOT NULL DEFAULT 0,
    likes_count INT NOT NULL DEFAULT 0,
    feedback_reason TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_videos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_videos_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE video_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    video_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_video_comments_video FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    CONSTRAINT fk_video_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE video_likes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    video_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_video_like (video_id, user_id),
    CONSTRAINT fk_video_likes_video FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    CONSTRAINT fk_video_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT DEFAULT NULL,
    due_date DATETIME NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'open',
    CONSTRAINT fk_assignments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    video_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED NOT NULL,
    score INT NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uniq_grade_video (video_id),
    CONSTRAINT fk_grades_video FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    CONSTRAINT fk_grades_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_grades_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    certificate_code VARCHAR(80) NOT NULL UNIQUE,
    pdf_path VARCHAR(255) DEFAULT NULL,
    issued_by INT UNSIGNED NOT NULL,
    issued_at DATETIME NOT NULL,
    CONSTRAINT fk_certificates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_issuer FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE badges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description TEXT DEFAULT NULL,
    icon VARCHAR(20) NOT NULL,
    points_required INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_badges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    badge_id INT UNSIGNED NOT NULL,
    awarded_at DATETIME NOT NULL,
    UNIQUE KEY uniq_user_badge (user_id, badge_id),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(180) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    room VARCHAR(120) DEFAULT 'Phòng Zoom A',
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_schedules_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_schedules_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE community_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    content TEXT DEFAULT NULL,
    post_type ENUM('text', 'image', 'video', 'emoji') NOT NULL DEFAULT 'text',
    media_path VARCHAR(255) DEFAULT NULL,
    emoji VARCHAR(20) DEFAULT '🎵',
    share_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_community_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE community_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_community_comments_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_community_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE community_likes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_community_like (post_id, user_id),
    CONSTRAINT fk_community_likes_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_community_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE friendships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    friend_id INT UNSIGNED NOT NULL,
    status ENUM('accepted', 'pending') NOT NULL DEFAULT 'accepted',
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_friendship (user_id, friend_id),
    CONSTRAINT fk_friendships_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_friendships_friend FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chat_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_chat_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    target_role VARCHAR(30) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE livestreams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    session_date DATETIME NOT NULL,
    stream_url VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('upcoming', 'live', 'ended') NOT NULL DEFAULT 'upcoming',
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rankings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED DEFAULT NULL,
    points INT NOT NULL DEFAULT 0,
    badges_count INT NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_rankings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rankings_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    setting_key VARCHAR(120) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, name, email, password, role, avatar_path, bio, points, locked, remember_token, created_at, updated_at) VALUES
(1, 'Administrator', 'admin@musicofeveryone.vn', '$2y$10$hs8ODo51s6.2BGJElLSqoOW.Ac0glJtYkrYgpqs9jgtTUNeOKTAum', 'admin', NULL, 'Quản trị hệ thống', 0, 0, NULL, '2024-04-01 08:00:00', '2024-04-01 08:00:00'),
(2, 'Minh Anh', 'minhanh@musicofeveryone.vn', '$2y$10$vL76hJtG4ZxNw0w4GKBFjOZPep3oHkNAHYHqyzEAuRCWLsuHeA3rS', 'student', NULL, 'Yêu piano và thanh nhạc.', 320, 0, NULL, '2024-04-02 09:00:00', '2024-04-02 09:00:00'),
(3, 'Lan Anh', 'lananh@musicofeveryone.vn', '$2y$10$vL76hJtG4ZxNw0w4GKBFjOZPep3oHkNAHYHqyzEAuRCWLsuHeA3rS', 'student', NULL, 'Đam mê guitar đệm hát.', 280, 0, NULL, '2024-04-03 09:00:00', '2024-04-03 09:00:00'),
(4, 'Tuấn Anh', 'tuananh@musicofeveryone.vn', '$2y$10$vL76hJtG4ZxNw0w4GKBFjOZPep3oHkNAHYHqyzEAuRCWLsuHeA3rS', 'student', NULL, 'Thích violin và hòa âm.', 360, 0, NULL, '2024-04-04 09:00:00', '2024-04-04 09:00:00'),
(5, 'Cô Hương', 'huong@musicofeveryone.vn', '$2y$10$bgCBVLDUVavCZu//IDYoIOut5MdAGtm9kDLZrTtxMrDHFbLVMLWHa', 'teacher', NULL, 'Giáo viên piano và thanh nhạc.', 0, 0, NULL, '2024-04-01 09:00:00', '2024-04-01 09:00:00'),
(6, 'Thầy Minh', 'thayminh@musicofeveryone.vn', '$2y$10$bgCBVLDUVavCZu//IDYoIOut5MdAGtm9kDLZrTtxMrDHFbLVMLWHa', 'teacher', NULL, 'Giáo viên guitar và violin.', 0, 0, NULL, '2024-04-01 10:00:00', '2024-04-01 10:00:00');

INSERT INTO courses (id, name, slug, description, cover_image, category, teacher_id, created_at) VALUES
(1, 'Piano Cơ Bản', 'piano-co-ban', 'Khóa học giúp người mới bắt đầu làm quen nốt nhạc, tiết tấu và phối hợp hai tay.', '🎹', 'Piano', 5, '2024-04-05 08:00:00'),
(2, 'Guitar Đệm Hát', 'guitar-dem-hat', 'Luyện hợp âm, tiết tấu và kỹ năng tự tin đệm hát các ca khúc phổ biến.', '🎸', 'Guitar', 6, '2024-04-05 08:15:00'),
(3, 'Thanh Nhạc', 'thanh-nhac', 'Rèn hơi thở, khẩu hình và kỹ thuật biểu diễn dành cho học viên yêu ca hát.', '🎤', 'Thanh nhạc', 5, '2024-04-05 08:30:00'),
(4, 'Violin Cơ Bản', 'violin-co-ban', 'Giới thiệu tư thế kéo vĩ, cảm âm và luyện ngón cơ bản cho violin.', '🎻', 'Violin', 6, '2024-04-05 08:45:00');

INSERT INTO lessons (id, course_id, title, description, lesson_date, video_url, created_at) VALUES
(1, 1, 'Bài 1: Làm quen phím đàn', 'Nhận diện quãng, nốt và vị trí ngồi đúng.', '2024-05-06 19:00:00', 'https://www.youtube.com/watch?v=5qap5aO4i9A', '2024-04-06 09:00:00'),
(2, 2, 'Bài 1: Bộ hợp âm cơ bản', 'Học 4 hợp âm phổ biến trong guitar đệm hát.', '2024-05-07 19:00:00', 'https://www.youtube.com/watch?v=jfKfPfyJRdk', '2024-04-06 09:10:00'),
(3, 3, 'Bài 1: Kiểm soát hơi thở', 'Luyện hơi thở bụng và thả lỏng vai cổ.', '2024-05-08 19:00:00', 'https://www.youtube.com/watch?v=DWcJFNfaw9c', '2024-04-06 09:20:00'),
(4, 4, 'Bài 1: Tư thế kéo vĩ', 'Thiết lập tư thế cầm đàn, giữ vĩ cơ bản.', '2024-05-09 19:00:00', 'https://www.youtube.com/watch?v=11x51U7X6g0', '2024-04-06 09:30:00');

INSERT INTO enrollments (id, user_id, course_id, progress, completed_at, created_at) VALUES
(1, 2, 1, 85.00, NULL, '2024-04-10 08:00:00'),
(2, 2, 3, 60.00, NULL, '2024-04-10 08:05:00'),
(3, 3, 2, 92.00, '2024-08-01 12:00:00', '2024-04-11 08:00:00'),
(4, 4, 4, 88.00, NULL, '2024-04-12 08:00:00'),
(5, 4, 1, 45.00, NULL, '2024-04-12 08:10:00');

INSERT INTO videos (id, user_id, course_id, title, description, file_path, thumbnail_path, mime_type, privacy, status, views, likes_count, feedback_reason, created_at, updated_at) VALUES
(1, 2, 1, 'Luyện ngón Hanon tuần 1', 'Bài thực hành luyện ngón tay phải và trái.', '/uploads/videos/sample-piano.mp4', NULL, 'video/mp4', 'community', 'approved', 240, 12, 'Giữ nhịp ổn định hơn nữa.', '2024-05-10 20:00:00', '2024-05-11 09:00:00'),
(2, 3, 2, 'Đệm hát ca khúc Nàng Thơ', 'Bài tập đổi hợp âm và giữ groove ổn định.', '/uploads/videos/sample-guitar.mp4', NULL, 'video/mp4', 'public', 'approved', 180, 9, NULL, '2024-05-11 20:00:00', '2024-05-12 09:00:00'),
(3, 4, 4, 'Bài kéo vĩ cơ bản', 'Em luyện bài kéo vĩ open string.', '/uploads/videos/sample-violin.mp4', NULL, 'video/mp4', 'community', 'pending', 22, 1, NULL, '2024-05-13 20:00:00', '2024-05-13 20:00:00'),
(4, 2, 3, 'Luyện thanh quãng 5', 'Video luyện hơi và lên nốt cao.', '/uploads/videos/sample-vocal.mp4', NULL, 'video/mp4', 'private', 'approved', 65, 4, 'Âm sắc tốt, cần mở khẩu hình hơn.', '2024-05-14 20:00:00', '2024-05-15 09:00:00');

INSERT INTO video_comments (id, video_id, user_id, comment, created_at) VALUES
(1, 1, 5, 'Em giữ nhịp tốt, tuần sau thử tăng tốc độ lên 80 BPM nhé.', '2024-05-11 08:30:00'),
(2, 1, 2, 'Em sẽ luyện thêm phần chuyển ngón ạ!', '2024-05-11 09:00:00'),
(3, 2, 6, 'Phần chuyển hợp âm khá chắc, chú ý nhấn nhịp 2 và 4.', '2024-05-12 08:30:00'),
(4, 4, 5, 'Tiếng head voice sáng, cần giữ hơi đều hơn.', '2024-05-15 08:30:00');

INSERT INTO video_likes (id, video_id, user_id, created_at) VALUES
(1, 1, 3, '2024-05-11 10:00:00'),
(2, 1, 4, '2024-05-11 10:05:00'),
(3, 2, 2, '2024-05-12 10:00:00');

INSERT INTO assignments (id, course_id, title, description, due_date, status) VALUES
(1, 1, 'Thu âm bài tập tiết tấu 4/4', 'Quay video 1 phút luyện nốt đen và móc đơn.', '2026-09-10 18:00:00', 'open'),
(2, 3, 'Luyện hơi bài vocalise', 'Gửi file video tập hơi và legato.', '2026-09-15 18:00:00', 'open'),
(3, 4, 'Bài tập kéo vĩ đều', 'Thực hành open string 60 BPM.', '2026-09-20 18:00:00', 'open');

INSERT INTO grades (id, video_id, student_id, teacher_id, score, comment, created_at, updated_at) VALUES
(1, 1, 2, 5, 88, 'Bản luyện tập vững vàng, biểu cảm tốt.', '2024-05-11 09:00:00', '2024-05-11 09:00:00'),
(2, 2, 3, 6, 91, 'Phần hát vào nhịp rất chắc, giữ phong độ nhé.', '2024-05-12 09:00:00', '2024-05-12 09:00:00'),
(3, 4, 2, 5, 84, 'Cần giữ hơi dài hơn ở đoạn cao trào.', '2024-05-15 09:00:00', '2024-05-15 09:00:00');

INSERT INTO certificates (id, user_id, course_id, certificate_code, pdf_path, issued_by, issued_at) VALUES
(1, 3, 2, 'MOE-20240801-LANANH', '/uploads/certificates/certificate-moe-20240801-lananh.pdf', 1, '2024-08-01 12:30:00');

INSERT INTO badges (id, name, description, icon, points_required) VALUES
(1, 'Khởi Đầu Rực Rỡ', 'Dành cho học viên đạt từ 80 điểm ở bài chấm đầu tiên.', '🌟', 80),
(2, 'Tay Đàn Chăm Chỉ', 'Hoàn thành đều đặn bài tập mỗi tuần.', '🎹', 90),
(3, 'Sân Khấu Tự Tin', 'Biểu diễn và chia sẻ tích cực trong cộng đồng.', '🎤', 100);

INSERT INTO user_badges (id, user_id, badge_id, awarded_at) VALUES
(1, 2, 1, '2024-05-11 10:00:00'),
(2, 3, 1, '2024-05-12 10:00:00'),
(3, 4, 2, '2024-05-20 10:00:00');

INSERT INTO schedules (id, course_id, teacher_id, title, subject, start_time, end_time, room, created_at) VALUES
(1, 1, 5, 'Piano Cơ Bản - Buổi 1', 'Làm quen phím đàn', '2024-05-06 19:00:00', '2024-05-06 20:30:00', 'Zoom Piano 1', '2024-04-20 09:00:00'),
(2, 2, 6, 'Guitar Đệm Hát - Buổi 1', 'Hợp âm cơ bản', '2024-05-07 19:00:00', '2024-05-07 20:30:00', 'Zoom Guitar 2', '2024-04-20 09:10:00'),
(3, 3, 5, 'Thanh Nhạc - Buổi 1', 'Khởi động và hơi thở', '2024-05-08 19:00:00', '2024-05-08 20:30:00', 'Zoom Vocal 1', '2024-04-20 09:20:00'),
(4, 4, 6, 'Violin Cơ Bản - Buổi 1', 'Tư thế kéo vĩ', '2024-05-09 19:00:00', '2024-05-09 20:30:00', 'Zoom Violin 1', '2024-04-20 09:30:00'),
(5, 1, 5, 'Piano Cơ Bản - Buổi 2', 'Luyện ngón và metronome', '2024-05-13 19:00:00', '2024-05-13 20:30:00', 'Zoom Piano 1', '2024-04-20 09:40:00');

INSERT INTO community_posts (id, user_id, content, post_type, media_path, emoji, share_count, created_at) VALUES
(1, 2, 'Hôm nay mình hoàn thành bài Hanon tuần 1 và bắt đầu thấy ngón linh hoạt hơn! Ai đang học piano cùng mình không?', 'text', NULL, '🎹', 4, '2024-05-11 11:00:00'),
(2, 3, 'Mình vừa thử đệm hát ca khúc mới, xin mọi người góp ý nhé!', 'video', '/uploads/videos/sample-guitar.mp4', '🎸', 2, '2024-05-12 11:30:00'),
(3, 4, 'Có ai có mẹo giữ vĩ violin thẳng hơn không ạ?', 'emoji', NULL, '🎻', 1, '2024-05-13 12:00:00');

INSERT INTO community_comments (id, post_id, user_id, comment, created_at) VALUES
(1, 1, 3, 'Mình cũng đang học piano, cùng cố gắng nhé!', '2024-05-11 12:00:00'),
(2, 2, 5, 'Video khá tốt, em chú ý phần vào nhịp đầu câu.', '2024-05-12 12:00:00'),
(3, 3, 6, 'Em thử giữ cổ tay mềm hơn khi kéo vĩ nhé.', '2024-05-13 12:30:00');

INSERT INTO community_likes (id, post_id, user_id, created_at) VALUES
(1, 1, 3, '2024-05-11 12:05:00'),
(2, 1, 4, '2024-05-11 12:06:00'),
(3, 2, 2, '2024-05-12 12:05:00');

INSERT INTO friendships (id, user_id, friend_id, status, created_at) VALUES
(1, 2, 3, 'accepted', '2024-05-01 09:00:00'),
(2, 2, 4, 'accepted', '2024-05-01 09:10:00'),
(3, 3, 2, 'accepted', '2024-05-01 09:00:00'),
(4, 4, 2, 'accepted', '2024-05-01 09:10:00');

INSERT INTO chat_messages (id, sender_id, receiver_id, message, is_read, created_at) VALUES
(1, 2, 5, 'Cô ơi, em nên tăng tốc độ metronome lên bao nhiêu cho bài Hanon?', 1, '2024-05-11 19:00:00'),
(2, 5, 2, 'Em thử tăng dần 5 BPM mỗi lần, ưu tiên đều nhịp trước nhé.', 1, '2024-05-11 19:10:00'),
(3, 3, 6, 'Thầy ơi em đau ngón khi bấm hợp âm F, có mẹo nào không ạ?', 0, '2024-05-12 19:00:00');

INSERT INTO notifications (id, user_id, title, message, link, is_read, target_role, created_at) VALUES
(1, 2, 'Chào mừng đến với musicofeveryone', 'Hãy bắt đầu bằng việc xem lịch học và nộp video đầu tiên của bạn.', '/student/dashboard.php', 0, 'student', '2024-05-01 08:00:00'),
(2, 3, 'Chứng chỉ mới sẵn sàng', 'Bạn có thể tải chứng chỉ khóa Guitar Đệm Hát trong hồ sơ.', '/student/profile.php', 0, 'student', '2024-08-01 13:00:00'),
(3, 4, 'Video mới đang chờ duyệt', 'Video violin của bạn đang được giáo viên xem xét.', '/student/video-library.php', 0, 'student', '2024-05-13 20:05:00');

INSERT INTO livestreams (id, title, session_date, stream_url, description, status, created_at) VALUES
(1, 'Livestream Hỏi Đáp Piano Tháng 9', '2026-09-05 20:00:00', 'https://www.youtube.com/watch?v=jfKfPfyJRdk', 'Giải đáp thắc mắc về ngón, nhịp và cách luyện piano hiệu quả tại nhà.', 'upcoming', '2026-08-15 10:00:00'),
(2, 'Workshop Guitar Đệm Hát Live', '2026-08-20 19:30:00', 'https://www.youtube.com/watch?v=5qap5aO4i9A', 'Buổi hướng dẫn giữ groove và thay hợp âm mượt mà.', 'live', '2026-08-15 10:10:00'),
(3, 'Violin Practice Room', '2026-07-25 19:00:00', 'https://www.youtube.com/watch?v=DWcJFNfaw9c', 'Replay buổi chỉnh tư thế kéo vĩ dành cho người mới.', 'ended', '2026-07-10 10:00:00');

INSERT INTO rankings (id, user_id, course_id, points, badges_count, updated_at) VALUES
(1, 4, NULL, 360, 1, '2024-08-01 13:00:00'),
(2, 2, NULL, 320, 1, '2024-08-01 13:00:00'),
(3, 3, NULL, 280, 1, '2024-08-01 13:00:00'),
(4, 2, 1, 938, 1, '2024-08-01 13:00:00'),
(5, 3, 2, 1011, 1, '2024-08-01 13:00:00'),
(6, 4, 4, 968, 1, '2024-08-01 13:00:00');

INSERT INTO settings (setting_key, setting_value, updated_at) VALUES
('contact_phone', '0909 123 456', '2024-04-01 08:00:00'),
('contact_email', 'hello@musicofeveryone.vn', '2024-04-01 08:00:00'),
('contact_address', '12 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh', '2024-04-01 08:00:00'),
('working_hours', '08:00 - 20:00 | Thứ 2 - Chủ nhật', '2024-04-01 08:00:00'),
('map_embed_url', 'https://www.google.com/maps?q=12%20Nguy%E1%BB%85n%20Hu%E1%BB%87%2C%20Qu%E1%BA%ADn%201%2C%20TP.%20H%E1%BB%93%20Ch%C3%AD%20Minh&output=embed', '2024-04-01 08:00:00'),
('map_lat', '10.7756587', '2024-04-01 08:00:00'),
('map_lng', '106.7004238', '2024-04-01 08:00:00'),
('facebook_url', 'https://facebook.com/musicofeveryone.vn', '2024-04-01 08:00:00'),
('youtube_url', 'https://youtube.com/@musicofeveryone', '2024-04-01 08:00:00'),
('tiktok_url', 'https://tiktok.com/@musicofeveryone', '2024-04-01 08:00:00');

SET FOREIGN_KEY_CHECKS = 1;
