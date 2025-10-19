drop table usersession cascade constraints;
drop table users cascade constraints;
drop table student cascade constraints;
drop table admin cascade constraints;
drop table role cascade constraints;
drop table courses cascade constraints;
drop table prerequisites cascade constraints;
drop table enrollments cascade constraints;
drop table sections cascade constraints;
drop view student_academic_info;
drop SEQUENCE enrollment_seq;
drop SEQUENCE student_seq;

-- Creating tables
create table role (
  rnumber number(5) primary key,
  rname varchar2(50) not null unique
);

create table users (
  username varchar2(8) primary key,
  password varchar2(12),
  rnumber number(5) not null references role
);	

create table usersession (
  sessionid varchar2(32) primary key,
  username varchar2(8),
  sessiondate date,
  foreign key (username) references users(username)
);

create table student (
  sid varchar2(100) primary key,
  fname varchar2(30) not null, 
  lname varchar2(30) not null, 
  admission_date date,
  rnumber number(5) not null references role,
  username varchar2(8) not null unique references users(username),
  age number,
  address VARCHAR(255),
  student_type VARCHAR(20),
  standing varchar2(20),
  concentration varchar2(20),
  probation_status CHAR(1) CHECK (probation_status IN ('Y', 'N'))
);

create table admin (
  aid number(10) primary key,
  fname varchar2(30) not null, 
  lname varchar2(30) not null, 
  start_date date,
  rnumber number(5) not null references role,
  username varchar2(8) not null references users(username)
);


CREATE TABLE courses (
    course_id VARCHAR(10) PRIMARY KEY,
    course_title VARCHAR(100),
    credits number(10),
    prerequisite_course_id VARCHAR(10)
);


CREATE TABLE prerequisites (
    course_id VARCHAR(10),
    prerequisite_course_id VARCHAR(10),
    PRIMARY KEY (course_id, prerequisite_course_id),
    FOREIGN KEY (course_id) REFERENCES courses(course_id),
    FOREIGN KEY (prerequisite_course_id) REFERENCES courses(course_id)
);


CREATE TABLE sections (
    section_id VARCHAR(10) PRIMARY KEY,
    course_id VARCHAR(10),
    semester VARCHAR(20),
    enrollment_deadline DATE,
    capacity number(10),
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
);





CREATE TABLE enrollments (
    enrollment_id varchar2(9),
    sid varchar2(100),
    section_id VARCHAR(10),
    grade varchar2(8),
    FOREIGN KEY (sid) REFERENCES student(sid),
    FOREIGN KEY (section_id) REFERENCES sections(section_id)
);





CREATE OR REPLACE VIEW student_academic_info AS
SELECT 
    s.sid, 
    s.fname, 
    s.lname, 
    s.probation_status,
    COUNT(CASE 
            WHEN sec.semester = 'Spring 2024' AND e.grade IS NOT NULL THEN e.section_id 
         END) AS courses_completed, 
    SUM(CASE 
            WHEN sec.semester = 'Spring 2024' AND e.grade IS NOT NULL THEN c.credits 
         END) AS total_credits, 
    SUM(
        CASE 
            WHEN sec.semester = 'Spring 2024' AND e.grade IS NOT NULL THEN 
                CASE 
                    WHEN e.grade = 'A' THEN 4.0 * c.credits
                    WHEN e.grade = 'B' THEN 3.0 * c.credits
                    WHEN e.grade = 'C' THEN 2.0 * c.credits
                    WHEN e.grade = 'D' THEN 1.0 * c.credits
                    WHEN e.grade = 'F' THEN 0.0 * c.credits
                END
         END
    ) / SUM(CASE 
                WHEN sec.semester = 'Spring 2024' AND e.grade IS NOT NULL THEN c.credits 
            END) AS GPA
FROM student s
JOIN enrollments e ON s.sid = e.sid
JOIN sections sec ON e.section_id = sec.section_id
JOIN courses c ON sec.course_id = c.course_id
GROUP BY s.sid, s.fname, s.lname,s.probation_status;


CREATE SEQUENCE enrollment_seq
  START WITH 28
  INCREMENT BY 1
  NOMAXVALUE
  CACHE 10;



CREATE OR REPLACE FUNCTION number_to_alphanumeric (n NUMBER)
  RETURN VARCHAR2 AS
  alnum VARCHAR2(9) := 'E';
  digit NUMBER;
BEGIN
  
  RETURN 'E' || LPAD(n, 3, '0');
END;
/






CREATE OR REPLACE TRIGGER trg_enrollment_id
BEFORE INSERT ON enrollments
FOR EACH ROW
WHEN (NEW.enrollment_id IS NULL)
BEGIN
  :NEW.enrollment_id := number_to_alphanumeric(enrollment_seq.NEXTVAL);
END;
/



CREATE SEQUENCE student_seq
  START WITH 123459
  INCREMENT BY 1
  NOMAXVALUE
  CACHE 10;

CREATE OR REPLACE FUNCTION generate_student_id (lname VARCHAR2)
  RETURN VARCHAR2 AS
  numeric_part VARCHAR2(6);
  generated_id VARCHAR2(8);
BEGIN
  -- Get the next value from the sequence
  numeric_part := TO_CHAR(student_seq.NEXTVAL, 'FM000000');

  -- Combine the first two letters of the last name with the numeric part
  generated_id := UPPER(SUBSTR(lname, 1, 2)) || numeric_part;

  RETURN generated_id;
END;
/


CREATE OR REPLACE PROCEDURE insert_student (
    fname IN VARCHAR2,
    lname IN VARCHAR2,
    admission_date IN DATE,
    rnumber IN NUMBER,
    username IN VARCHAR2,
    age IN NUMBER,
    address IN VARCHAR2,
    student_type IN VARCHAR2,
    standing in varchar2,
    concentration in varchar2,
    probation_status IN CHAR
) AS
    sid VARCHAR2(10); 
BEGIN
    sid := generate_student_id(lname);

    INSERT INTO student (
        sid, fname, lname, admission_date, rnumber, username, age, address, student_type, standing,concentration,probation_status
    ) VALUES (
        sid, fname, lname, admission_date, rnumber, username, age, address, student_type, standing,concentration,probation_status
    );

    DBMS_OUTPUT.PUT_LINE('Student inserted successfully with SID: ' || sid);

EXCEPTION
    WHEN OTHERS THEN
        -- Step 4: Handle any exception
        ROLLBACK; -- Rollback the transaction in case of failure
        DBMS_OUTPUT.PUT_LINE('Error inserting student: ' || SQLERRM);
END;
/
















-- Inserting data
insert into role (rnumber, rname) values (1, 'admin');
insert into role (rnumber, rname) values (2, 'student');
insert into role (rnumber, rname) values (3, 'student-admin');

insert into users (username, password, rnumber) values ('a', 'a', 1);
insert into users (username, password, rnumber) values ('b', 'b', 2);
insert into users (username, password, rnumber) values ('c', 'c', 3);
insert into users (username, password, rnumber) values ('d', 'd', 2);


insert into admin (aid, fname, lname, start_date, rnumber, username) values (1, 'a', 'b', to_date('12/01/2000', 'mm/dd/yyyy'), 1, 'a');
insert into admin (aid, fname, lname, start_date, rnumber, username) values (3, 'e', 'f', to_date('12/01/1999', 'mm/dd/yyyy'), 3, 'c');


INSERT INTO student (sid, fname, lname, admission_date, rnumber, username, age, address, student_type,standing,concentration, probation_status) VALUES ('DO123456', 'John', 'Doe', TO_DATE('2021-10-01', 'YYYY-MM-DD'), 2, 'd', 21, '123 Elm St, Springfield', 'Undergraduate','senior',NULL, 'N');
INSERT INTO student (sid, fname, lname, admission_date, rnumber, username, age, address, student_type,standing,concentration, probation_status) VALUES ('SM123457', 'Jane', 'Smith', TO_DATE('2020-09-01', 'YYYY-MM-DD'), 2, 'b', 22, '456 Oak St, Springfield', 'Graduate',NULL,'cloud','Y');
INSERT INTO student (sid, fname, lname, admission_date, rnumber, username, age, address, student_type,standing,concentration, probation_status) VALUES ('JO123458', 'Emily', 'Johnson', TO_DATE('2019-09-01', 'YYYY-MM-DD'), 2, 'c', 23, '789 Pine St, Springfield', 'Undergraduate','junior',NULL,'Y');


INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5023', 'Programming Languages', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5033', 'Concepts Of Artificial Intelligence', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5043', 'Applications Database Systems', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5053', 'Operating Systems', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5063', 'Computer Networks', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5073', 'Translator Design', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5083', 'Cybersecurity', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5113', 'Structured Design', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5133', 'Theory Of Data Base Systems', 3, 'CMSC 5043');
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5143', 'Algorithms for Machine Learning', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5163', 'Secure System Administration', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5193', 'Introduction to Robotics', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5213', 'Front End Web Programming', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5223', 'Cyber Infrastructure and Cloud Computing', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5243', 'Artificial Intelligence', 3, 'CMSC 5033');
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5273', 'Theory of Computing', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5283', 'Software Engineering I', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5313', 'Internet of Things', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5323', 'Network Security', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5333', 'Incident Analysis and Response I', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5343', 'Cyber Operations', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5353', 'Incident Analysis and Response II', 3, 'CMSC 5333');
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5373', 'Cloud Web Apps Development', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5401', 'Ethics in Computing', 1, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5423', 'Software Engineering II', 3, 'CMSC 5283');
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5613', 'Algorithm Design and Implementation', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5900', 'Practicum In Computing Science', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5910', 'Seminar / Special Topics', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5950', 'Internship in Computer Science', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5980', 'Graduate Project', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 5990', 'Thesis', 6, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 2613', 'Computer Science Fundamentals', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('MATH 2313', 'Mathematics for Computer Science', 3, NULL);
INSERT INTO courses (course_id, course_title, credits, prerequisite_course_id) VALUES ('CMSC 3613', 'Algorithm Design and Implementation', 3, NULL);




INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5043', 'CMSC 2613');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5043', 'MATH 2313');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5053', 'CMSC 2613');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5063', 'CMSC 2613');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5073', 'CMSC 3613');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5083', 'CMSC 2613');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5423', 'CMSC 5283');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5143', 'CMSC 5033');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5223', 'CMSC 2613');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5353', 'CMSC 5333');
INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES ('CMSC 5313', 'CMSC 3613');


INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES    ('SEC01', 'CMSC 5043', 'Fall 2024', TO_DATE('2024-09-01', 'YYYY-MM-DD'), 30);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC02', 'CMSC 5283', 'Fall 2024', TO_DATE('2024-09-01', 'YYYY-MM-DD'), 25);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC03', 'CMSC 5613', 'Fall 2024', TO_DATE('2024-09-01', 'YYYY-MM-DD'), 40);
  INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES    ('SEC04', 'CMSC 5900', 'Fall 2024', TO_DATE('2024-09-01', 'YYYY-MM-DD'), 20);
  INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES    ('SEC05', 'CMSC 5950', 'Fall 2024', TO_DATE('2024-09-01', 'YYYY-MM-DD'), 15);
  INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES   ('SEC06', 'CMSC 5980', 'Fall 2024', TO_DATE('2024-09-01', 'YYYY-MM-DD'), 30);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC07', 'CMSC 5990', 'Fall 2024', TO_DATE('2024-09-01', 'YYYY-MM-DD'), 25);

INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC08', 'CMSC 5213', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 40);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES      ('SEC10', 'CMSC 5373', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 15);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC11', 'CMSC 5223', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 30);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC12', 'CMSC 5313', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 25);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC13', 'CMSC 5083', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 40);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES      ('SEC14', 'CMSC 5423', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 20);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC16', 'CMSC 5033', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 30);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC17', 'CMSC 5143', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 25);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC18', 'CMSC 5273', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 40);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC19', 'CMSC 5023', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 20);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC20', 'CMSC 5053', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 15);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC21', 'CMSC 5063', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 30);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC22', 'CMSC 5073', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 25);
 INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC23', 'CMSC 5353', 'Spring 2025', TO_DATE('2025-01-10', 'YYYY-MM-DD'), 40);

INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES ('SEC24', 'CMSC 5193', 'Spring 2024', TO_DATE('2024-04-01', 'YYYY-MM-DD'), 30);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES  ('SEC25', 'CMSC 5223', 'Spring 2024', TO_DATE('2024-04-01', 'YYYY-MM-DD'), 25);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES  ('SEC26', 'CMSC 5613', 'Spring 2024', TO_DATE('2024-04-01', 'YYYY-MM-DD'), 40);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES      ('SEC28', 'CMSC 5053', 'Spring 2024', TO_DATE('2024-04-01', 'YYYY-MM-DD'), 35);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES     ('SEC29', 'CMSC 5023', 'Spring 2024', TO_DATE('2024-04-01', 'YYYY-MM-DD'), 20);
INSERT INTO sections (section_id, course_id, semester, enrollment_deadline, capacity) VALUES      ('SEC30', 'CMSC 5401', 'Spring 2024', TO_DATE('2024-04-01', 'YYYY-MM-DD'), 10);





INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E01', 'DO123456', 'SEC24', 'B'); 
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E02', 'DO123456', 'SEC25', 'C');  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E03', 'DO123456', 'SEC26', 'A');  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E04', 'DO123456', 'SEC04', null);  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E05', 'DO123456', 'SEC02', null); 
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E06', 'DO123456', 'SEC01', null); 



INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E07', 'SM123457', 'SEC02', null);  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E08', 'SM123457', 'SEC05', null);  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E09', 'SM123457', 'SEC07', null); 
  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E10', 'SM123457', 'SEC24', 'A');  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E11', 'SM123457', 'SEC25', 'B');  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E12', 'SM123457', 'SEC26', 'C');   



INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E13', 'JO123458', 'SEC30', 'A');

INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E14', 'JO123458', 'SEC28', 'B');  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E15', 'JO123458', 'SEC01', null);  

INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E16', 'JO123458', 'SEC03', null);  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E17', 'JO123458', 'SEC06', null);  
INSERT INTO enrollments (enrollment_id, sid, section_id, grade) VALUES ('E18', 'JO123458', 'SEC29', 'C');  






commit;