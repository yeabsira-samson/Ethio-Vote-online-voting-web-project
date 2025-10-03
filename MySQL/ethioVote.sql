create database Ethiovote
use Ethiovote
-- Table
CREATE TABLE People (
    people_id INT PRIMARY KEY,
    first_name VARCHAR(15) NOT NULL,
    middle_name VARCHAR(15) NOT NULL,
    last_name VARCHAR(15) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    email VARCHAR(30) NOT NULL UNIQUE,
    phone_number VARCHAR(10),
    fin VARCHAR(6) NOT NULL UNIQUE,
    date_of_birth DATE NOT NULL
)
CREATE  TABLE Voter (
    voter_id INT AUTO_INCREMENT PRIMARY KEY,
    voter_identification VARCHAR(5) UNIQUE NOT NULL,
    password VARCHAR(10) NOT NULL unique,
    status VARCHAR(10) DEFAULT 'logout',
    voting_status VARCHAR(10) DEFAULT 'not voted',
    fin VARCHAR(6) NOT NULL,
    FOREIGN KEY (fin) REFERENCES People(fin),
)
-- Update
ADD CONSTRAINT uq_voter_password UNIQUE (password);
ALTER TABLE Voter
MODIFY COLUMN status VARCHAR(10) DEFAULT 'loggedout';

CREATE TABLE Candidate(
Candidate_id INT PRIMARY KEY,
first_name  VARCHAR(15) NOT NULL,
last_name VARCHAR(15) NOT NULL,
gender VARCHAR(10) NOT NULL,
Position varchar(10) NOT NULL,
Party_name varchar(100) NOT NULL,
date_of_birth DATE NOT NULL
)
ALTER TABLE Candidate
ADD COLUMN total_vote_number INT DEFAULT 0;

CREATE TABLE OTP_verification (
    otp_id INT AUTO_INCREMENT PRIMARY KEY,
    otp_code VARCHAR(10) NOT NULL,
    expire_date_and_time DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'not expired',
    voter_identification VARCHAR(10) NOT NULL,
    FOREIGN KEY (voter_identification) REFERENCES Voter(voter_identification)
)
ALTER TABLE OTP_verification
MODIFY COLUMN voter_identification VARCHAR(10) NOT NULL,
ADD CONSTRAINT fk_voter_id
FOREIGN KEY (voter_identification) REFERENCES Voter(voter_identification);

CREATE TABLE Feedback(
 feedback_id INT AUTO_INCREMENT PRIMARY KEY,
 feedback text
)
-- Insert 
INSERT INTO Candidate( Candidate_id,first_name,last_name,gender,Position,Party_name,
date_of_birth) VALUES
(1,'Amira',' Bekele','Female','PM','Unity Progress Party ','1992-03-15'),
(2,'Dawit','Alemu','Male','PM','Green Future Movement','1991-03-15'),
(3,'Selam','Tadesse','Female','PM','People’s Freedom Alliance','1990-03-15'),
(4,'Kebede','Meles','Male','PM','Innovation & Technology Party','1989-03-15')
INSERT INTO People (
    people_id, first_name, middle_name, last_name, gender, email, phone_number, fin, date_of_birth
) VALUES
(1, 'Abebe', 'Kebede', 'Teshome', 'Male', 'abebe@gmail.com', '0911223344', '251001', '1992-03-15'),
(2, 'Almaz', 'Mekonnen', 'Bekele', 'Female', 'almaz@gmail.com', '0922334455', '251002', '1995-07-22'),
(3, 'Haile', 'Gebre', 'Selassie', 'Male', 'haile@gmail.com', '0933445566', '251003', '1983-11-18'),
(4, 'Mulu', 'Abate', 'Worku', 'Female', 'mulu.worku@gmail.com', '0944556677', '251004', '1998-01-05'),
(5, 'Tesfaye', 'Alemu', 'Gebremariam', 'Male', 'tesfaye@gmail.com', '0955667788', '251005', '1990-09-30'),
(6,'Yeabsira','Samson','Seleshi','Female','yabu@gmail.com','0924409318','251006','2004-05-13')

INSERT INTO People (
    people_id, first_name, middle_name, last_name, gender, email, phone_number, fin, date_of_birth
) VALUES
(7, 'ava', 'Tadele', 'Fikre', 'Fmale', 'heven3518@gmail.com', '0966778899', '251007', '2004-5-13'),
(8, 'Lily', 'Bekele', 'Abebe', 'Female', 'lily.abebe@gmail.com', '0977889900', '251008', '2008-06-15')

INSERT INTO People (
    people_id, first_name, middle_name, last_name, gender, email, phone_number, fin, date_of_birth
) VALUES
(9, 'Kidan', 'Berhane', 'Mekonnen', 'Female', 'kidan.mekonnen@yahoo.com', '0920123456', '251009', '2000-09-10'),
(10, 'Girma', 'Worku', 'Demisse', 'Male', 'girma.demisse@gmail.com', '0931234567', '251010', '1978-06-20'),
(11, 'Eyerusalem', 'Daniel', 'Tesfaye', 'Female', 'eyerusalem.tesfaye@outlook.com', '0942345678', '251011', '1995-02-28');



-- procedures 


-- logout
DELIMITER $$
CREATE PROCEDURE Logout_status(
    IN log VARCHAR(10)
)
BEGIN
    UPDATE voter
    SET status = 'loggedout'
    WHERE voter_identification = log;
END$$
DELIMITER ;


-- update password
DELIMITER $$

CREATE PROCEDURE Update_password(
    IN user_email VARCHAR(30),
    IN new_pass VARCHAR(255)  
)
BEGIN
    DECLARE v_id VARCHAR(10);

    SELECT v.voter_identification INTO v_id FROM Voter v
    INNER JOIN People p ON v.fin = p.fin
    WHERE p.email = user_email LIMIT 1;

    IF v_id IS NOT NULL THEN
        UPDATE Voter
        SET password = new_pass
        WHERE voter_identification = v_id;

        SELECT 'Updated successfully' AS Message;
    ELSE
        SELECT 'No voter found for this email' AS Message;
    END IF;
END $$

DELIMITER ;


-- submiting form
DELIMITER $$

CREATE PROCEDURE Registration_submit(
    IN p_Fin VARCHAR(10),
    IN p_Password VARCHAR(255) 
)
proc: BEGIN
    DECLARE v_VoterID VARCHAR(10);
    DECLARE v_UserAge INT;
    DECLARE v_FinExists INT DEFAULT 0;
    DECLARE v_VoterExists INT DEFAULT 0;
    DECLARE v_Attempt INT DEFAULT 0;


    IF p_Fin = '' OR p_Password = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'FIN or Password cannot be empty.';
    END IF;

    SET p_Fin = TRIM(p_Fin);

    SELECT COUNT(*) INTO v_FinExists
    FROM People
    WHERE TRIM(FIN) = p_Fin;

    IF v_FinExists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'FIN not found in People table.';
    END IF;

    SELECT COUNT(*) INTO v_VoterExists
    FROM Voter
    WHERE TRIM(FIN) = p_Fin;

    IF v_VoterExists > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'You are already registered.';
    END IF;


    SELECT TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())
    INTO v_UserAge
    FROM People
    WHERE TRIM(FIN) = p_Fin
    LIMIT 1;

    IF v_UserAge < 18 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'You must be at least 18 years old to register.';
    END IF;

    
    generate_vid: LOOP
        SET v_VoterID = CONCAT('VR', LPAD(FLOOR(RAND() * 10000), 4, '0')); 
        SELECT COUNT(*) INTO v_VoterExists
        FROM Voter
        WHERE Voter_identification = v_VoterID;

        IF v_VoterExists = 0 THEN
            LEAVE generate_vid;
        END IF;

        SET v_Attempt = v_Attempt + 1;
        IF v_Attempt > 100 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Unable to generate unique Voter ID.';
        END IF;
    END LOOP generate_vid;

    -- for data integrity
    START TRANSACTION;
    INSERT INTO Voter (Voter_identification, password, FIN)
    VALUES (v_VoterID, p_Password, p_Fin);
    
    IF ROW_COUNT() = 1 THEN
        COMMIT;
        SELECT CONCAT('Inserted Successfully! Your Voter ID is ', v_VoterID) AS Message;
    ELSE
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Failed to insert voter record.';
    END IF;
END$$

DELIMITER ;


-- to get result 
DELIMITER $$
CREATE PROCEDURE GetAllPartyVotes()
BEGIN
    SELECT Party_name, total_vote_number AS total_votes
    FROM Candidate;
END $$
DELIMITER ;

-- to vote

DELIMITER $$
CREATE PROCEDURE My_Vote(
    IN id INT,
    IN voter_ident VARCHAR(10)
)
BEGIN
    DECLARE current_status VARCHAR(10);

    SELECT voting_status INTO current_status
    FROM Voter
    WHERE voter_identification = voter_ident;

    IF current_status = 'not voted' THEN
        UPDATE Candidate
        SET total_vote_number = total_vote_number + 1
        WHERE candidate_id = id;

        UPDATE Voter
        SET voting_status = 'voted'
        WHERE voter_identification = voter_ident;

        SELECT 'Thank you for your vote' AS Message;
    ELSE
        -- Already voted
        SELECT 'You have already voted!' AS Message;
    END IF;
END $$
DELIMITER ;



-- to check email and generate otp 
DELIMITER $$

CREATE PROCEDURE Check_Email_And_Generate_OTP(
    IN email_input VARCHAR(100),
    OUT otp_out VARCHAR(10),
    OUT result INT
)
BEGIN
    DECLARE otp_code VARCHAR(6);
    DECLARE v_voter_id VARCHAR(20);

    -- if not found
    SET result = 0;
    SET otp_out = NULL;

    SELECT v.voter_identification
    INTO v_voter_id
    FROM Voter v
    JOIN People p ON v.fin = p.fin
    WHERE p.email = email_input
    LIMIT 1;

    
    IF v_voter_id IS NOT NULL THEN
        SET result = 1;
        -- Generate a 6-digit OTP
        SET otp_code = LPAD(FLOOR(RAND() * 1000000), 4, '0');
        SET otp_out = otp_code;

        -- Insert in to table and the otp will expire after 1 min
        INSERT INTO OTP_verification (otp_code, expire_date_and_time, status, voter_identification)
        VALUES (otp_code, NOW() + INTERVAL 1 MINUTE, 'not expired', v_voter_id)
        ON DUPLICATE KEY UPDATE
            otp_code = VALUES(otp_code),
            expire_date_and_time = VALUES(expire_date_and_time),
            status = 'not expired';
    END IF;

END$$

DELIMITER ;

-- to check if the otp is not expird or invalid
DELIMITER $$

CREATE PROCEDURE Verify_otp(
  IN OTPcode VARCHAR(4),
  OUT otp_status VARCHAR(20)
)
BEGIN
  IF EXISTS (SELECT 1 FROM OTP_verification WHERE OTPCode = OTPcode 
             AND status = 'not expired') THEN
    SET otp_status = 'Verified';
  ELSE
    SET otp_status = 'Not Verified';
  END IF;
END $$
DELIMITER ;


-- Enable the event scheduler to make the otp expire 
SET GLOBAL event_scheduler = ON;
SHOW VARIABLES LIKE 'event_scheduler';
CREATE EVENT IF NOT EXISTS expire_otp_event
ON SCHEDULE EVERY 1 MINUTE
DO
    UPDATE OTP_verification
    SET status = 'Expired'
    WHERE expire_date_and_time <= NOW()
      AND status <> 'Expired';


-- update
ALTER TABLE Voter MODIFY COLUMN voter_identification VARCHAR(20);
ALTER TABLE Voter MODIFY COLUMN password VARCHAR(255);
MODIFY COLUMN voter_identification VARCHAR(10) NOT NULL,
ADD CONSTRAINT fk_voter_id
FOREIGN KEY (voter_identification) REFERENCES Voter(voter_identification);

-- select 
select * from People
select * from candidate
select * from voter
select * from feedback
select * from  OTP_verification
