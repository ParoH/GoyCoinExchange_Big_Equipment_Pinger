-- ============================================================
--  Equipment Tracker Database
--  Compatible with XAMPP / MySQL 5.7+
-- ============================================================

CREATE DATABASE IF NOT EXISTS equipment_tracker
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE equipment_tracker;

-- ------------------------------------------------------------
--  Roles
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    role_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  Users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id    INT UNSIGNED NOT NULL,
    first_name VARCHAR(50)  NOT NULL,
    last_name  VARCHAR(50)  NOT NULL,
    pin_code   CHAR(6)      NOT NULL,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id)
        REFERENCES roles (role_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  Equipment
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipment (
    equipment_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_tag     VARCHAR(50)    NOT NULL UNIQUE,
    category      VARCHAR(50)    NOT NULL,
    status        ENUM('available','in_use','maintenance','retired')
                  NOT NULL DEFAULT 'available',
    current_lat   DECIMAL(10,7)  DEFAULT NULL,
    current_long  DECIMAL(10,7)  DEFAULT NULL,
    last_ping_time DATETIME      DEFAULT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  Sites
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sites (
    site_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_name       VARCHAR(100)  NOT NULL,
    center_lat      DECIMAL(10,7) NOT NULL,
    center_long     DECIMAL(10,7) NOT NULL,
    geofence_radius DECIMAL(10,2) NOT NULL COMMENT 'Radius in metres'
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  Assignments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS assignments (
    assignment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id  INT UNSIGNED NOT NULL,
    site_id       INT UNSIGNED NOT NULL,
    dispatched_by INT UNSIGNED NOT NULL,
    start_date    DATE         NOT NULL,
    end_date      DATE         DEFAULT NULL,
    CONSTRAINT fk_assign_equipment  FOREIGN KEY (equipment_id)
        REFERENCES equipment (equipment_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_assign_site FOREIGN KEY (site_id)
        REFERENCES sites (site_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_assign_dispatcher FOREIGN KEY (dispatched_by)
        REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  Key Checkouts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS key_checkouts (
    checkout_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id     INT UNSIGNED NOT NULL,
    operator_id      INT UNSIGNED NOT NULL,
    checkout_time    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    checkin_time     DATETIME     DEFAULT NULL,
    pre_inspect_log  TEXT         DEFAULT NULL,
    post_inspect_log TEXT         DEFAULT NULL,
    CONSTRAINT fk_checkout_equipment FOREIGN KEY (equipment_id)
        REFERENCES equipment (equipment_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_checkout_operator FOREIGN KEY (operator_id)
        REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
