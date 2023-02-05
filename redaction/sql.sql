CREATE TABLE version(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    name varchar(20),
    stat INT NOT NULL,
    dat DATE NOT NULL,
    version INT NOT NULL
)
CREATE TABLE reviewer(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    username varchar(20) NOT NULL,
    passwd varchar(20) NOT NULL,
    step varchar(20) NOT NULL,
    email varchar(20) NOT NULL
)
CREATE TABLE redactor(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    username varchar(20) NOT NULL,
    passwd varchar(20) NOT NULL,
    step varchar(20) NOT NULL,
    email varchar(20) NOT NULL
)
CREATE TABLE corrector(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    username varchar(20) NOT NULL,
    passwd varchar(20) NOT NULL,
    email varchar(20) NOT NULL
)
CREATE TABLE plan_num(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    name varchar(20),
    number_of_articles INT NOT NULL
)
CREATE TABLE fills(
    id_ver INT NOT NULL,
    FOREIGN KEY (id_ver)  REFERENCES version (id) ON DELETE CASCADE,
    id_plan INT NOT NULL,
    FOREIGN KEY (id_plan)  REFERENCES plan_num (id)ON DELETE CASCADE
    
)

CREATE TABLE prev_nex_ver(
    id_prev INT,
    FOREIGN KEY (id_prev)  REFERENCES version (id) ON DELETE CASCADE,
    id_next INT,
    FOREIGN KEY (id_next)  REFERENCES version (id) ON DELETE CASCADE

)
CREATE TABLE problem_list(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    txt TEXT,
    id_ver INT NOT NULL,
    FOREIGN KEY (id_ver)  REFERENCES version (id) ON DELETE CASCADE
 
)
CREATE TABLE problem_list_corr(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    txt TEXT,
    id_ver INT NOT NULL,
    FOREIGN KEY (id_ver)  REFERENCES version (id) ON DELETE CASCADE

)

CREATE TABLE review(
    id INT NOT NULL AUTO INCREMENT,
    PRIMARY KEY(id),
    ver_id INT NOT NULL,
    FOREIGN KEY (ver_id)  REFERENCES version (id) ON DELETE CASCADE,
    rev_id INT NOT NULL,
    FOREIGN KEY (rev_id)  REFERENCES reviewer (id) ON DELETE CASCADE,
)
CREATE TABLE send_recive(
    id_a INT NOT NULL,
    FOREIGN KEY (id_a)  REFERENCES author(id) ON DELETE CASCADE,
    id_ver INT NOT NULL,
    FOREIGN KEY (id_ver)  REFERENCES version(id) ON DELETE CASCADE,
    sends BOOLEAN DEFAULT SET NULL
)

CREATE TABLE version_corrector(
    id_ver INT NOT NULL,
    FOREIGN KEY (id_ver)  REFERENCES version (id) ON DELETE CASCADE,
    id_cor INT NOT NULL,
    FOREIGN KEY (id_cor)  REFERENCES corrector(id) ON DELETE CASCADE

)
CREATE TABLE version_redactor(
    id_ver INT NOT NULL,
    FOREIGN KEY (id_ver)  REFERENCES version (id) ON DELETE CASCADE,
    id_red INT NOT NULL,
    FOREIGN KEY (id_red)  REFERENCES redactor(id) ON DELETE CASCADE
)
CREATE TABLE reviewer(
    ver_id INT NOT NULL,
    FOREIGN KEY (ver_id)  REFERENCES version (id) ON DELETE CASCADE,
    id_red INT NOT NULL,
    FOREIGN KEY (red_id)  REFERENCES reviewer(id) ON DELETE CASCADE
)
CREATE TRIGGER counter AFTER insert ON version FOR EACH ROW BEGIN SELECT COUNT(*) INTO @a FROM version WHERE stat>0; INSERT INTO allinformation(active_articles) VALUE (@a); END;
CREATE TRIGGER counter1 AFTER insert ON version FOR EACH ROW BEGIN SELECT COUNT(*) INTO @a FROM fills WHERE stat>0; INSERT INTO allinformation(written_articles) VALUE (@a); END;
CREATE TRIGGER counter AFTER insert ON version FOR EACH ROW BEGIN SELECT COUNT(*) INTO @a FROM version WHERE stat>0; INSERT INTO allinformation(active_articles) VALUE (@a); END;


INSERT INTO author(username, email, pas, reg_date, deleted) VALUES
    ('newauthor', 'new_author@mail.ru', 'new12345', '2023-01-04', 0);
INSERT INTO reviewer(username, passwd, step, email) VALUES
    ('reviewer1', 'qwerty', 'professor', 'review@mail.ru');
INSERT INTO corrector(username, passwd, email) VALUES
    ('corrector1','abcdef','cor1@mail.ru');
INSERT INTO redactor(username, passwd, step, email) VALUES
    ('redactor1', '12345', 'professor', 'vfk@mail.ru'),
    ('redactor2', '123456', 'professor', 'mvf@mail.ru');
INSERT INTO version(name, stat, dat, version_number) VALUES
    ('newversion_r', 1, '2023-02-05',1),
    ('newversion_v', 3, '2023-02-05',1),
    ('newversion_c', 4, '2023-02-05',1),
    ('newversion_e', 11, '2023-02-05',1);
INSERT INTO prev_next_ver(id_next) VALUES
	((SELECT id FROM version WHERE name='newversion_r')),
    ((SELECT id FROM version WHERE name='newversion_v')),
    ((SELECT id FROM version WHERE name='newversion_c'));

INSERT INTO send_recive(id_a, id_ver, sends) VALUES
	(4, (SELECT id FROM version WHERE name='newversion_r'), 0),
    (4, (SELECT id FROM version WHERE name='newversion_v'), 0),
    (4, (SELECT id FROM version WHERE name='newversion_c'), 0),
    (4, (SELECT id FROM version WHERE name='newversion_e'), 0);

INSERT INTO version_corrector VALUES
	(1, (SELECT id FROM version WHERE name='newversion_c'));

INSERT INTO version_redactor VALUES
	(1, (SELECT id FROM version WHERE name='newversion_r'));

INSERT INTO version_reviewer() VALUES
    (1, (SELECT id FROM version WHERE name='newversion_v'));




