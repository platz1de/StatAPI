-- #!mysql
-- #{statapi
-- #    {init
-- #        {modules
CREATE TABLE IF NOT EXISTS modules
(
    name        VARCHAR(255) NOT NULL,
    displayName VARCHAR(255)          DEFAULT '',
    visible     BOOL         NOT NULL DEFAULT true,
    PRIMARY KEY (name)
);
-- #        }
-- #        {stats
CREATE TABLE IF NOT EXISTS stats
(
    name         VARCHAR(255) NOT NULL,
    module       VARCHAR(255) NOT NULL,
    type         INT(11) UNSIGNED      DEFAULT 0,
    displayType  INT(11) UNSIGNED      DEFAULT 0,
    defaultValue VARCHAR(255)          DEFAULT '0',
    displayName  VARCHAR(255)          DEFAULT '',
    visible      BOOL         NOT NULL DEFAULT true,
    position     INT(11) UNSIGNED      DEFAULT 0,
    PRIMARY KEY (name, module)
);
-- #        }
-- #        {data
CREATE TABLE IF NOT EXISTS data
(
    player VARCHAR(16) NOT NULL,
    stat   VARCHAR(255),
    module VARCHAR(255),
    score  VARCHAR(255),
    PRIMARY KEY (player, stat)
);
-- #        }
-- #    }
-- #    {register
-- #        {module
-- #            :module string
INSERT IGNORE INTO modules (
    name
)
VALUES (
           :module
       );
-- #        }
-- #        {stat
-- #            :stat string
-- #            :module string
INSERT IGNORE INTO stats (name,
                          module,
                          position)
VALUES (:stat,
        :module,
        IFNULL((SELECT * FROM (SELECT MAX(position) FROM stats WHERE module = :module)maxpos), 0) + 1);
-- #        }
-- #    }
-- #    {unregister
-- #        {module
-- #            :module string
DELETE
FROM modules
WHERE name = :module;
-- #        }
-- #        {stat
-- #            :stat string
-- #            :module string
DELETE
FROM stats
WHERE name = :stat
  AND module = :module;
-- #        }
-- #    }
-- #    {set
-- #        {module
-- #            {displayName
-- #                :module string
-- #                :displayName string
UPDATE modules
SET displayName = :displayName
WHERE name = :module;
-- #            }
-- #            {visible
-- #                :module string
-- #                :visible bool
UPDATE modules
SET visible = :visible
WHERE name = :module;
-- #            }
-- #        }
-- #        {stat
-- #            {type
-- #                :stat string
-- #                :module string
-- #                :type int
UPDATE stats
SET type = :type
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {displayType
-- #                :stat string
-- #                :module string
-- #                :displayType int
UPDATE stats
SET displayType = :displayType
WHERE name = :stat
  AND module = :module;
-- #            }

-- #            {default
-- #                :stat string
-- #                :module string
-- #                :default string
UPDATE stats
SET defaultValue = :default
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {displayName
-- #                :stat string
-- #                :module string
-- #                :displayName string
UPDATE stats
SET displayName = :displayName
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {visible
-- #                :stat string
-- #                :module string
-- #                :visible bool
UPDATE stats
SET visible = :visible
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {position
-- #                :stat string
-- #                :module string
-- #                :position int
UPDATE stats
SET position = :position
WHERE name = :stat
  AND module = :module;
-- #            }
-- #        }
-- #        {data
-- #            {score
-- #                {set
-- #                    :player string
-- #                    :stat string
-- #                    :module string
-- #                    :score string
INSERT INTO data (player,
                  stat,
                  module,
                  score)
VALUES (:player,
        :stat,
        :module,
        :score)
ON DUPLICATE KEY UPDATE score = VALUES(score);
-- #                }
-- #                {increase
-- #                    :player string
-- #                    :stat string
-- #                    :module string
-- #                    :score string
INSERT INTO data (player,
                  stat,
                  module,
                  score)
VALUES (:player,
        :stat,
        :module,
        (SELECT defaultValue FROM stats WHERE name = :stat AND module = :module) + :score)
ON DUPLICATE KEY
    UPDATE score = score + :score;
-- #                }
-- #                {decrease
-- #                    :player string
-- #                    :stat string
-- #                    :module string
-- #                    :score string
INSERT INTO data (player,
                  stat,
                  module,
                  score)
VALUES (:player,
        :stat,
        :module,
        (SELECT defaultValue FROM stats WHERE name = :stat AND module = :module) - :score)
ON DUPLICATE KEY
    UPDATE score = score + :score;
-- #                }
-- #                {highest
-- #                    :player string
-- #                    :stat string
-- #                    :module string
-- #                    :score string
INSERT INTO data(player,
                 stat,
                 module,
                 score)
VALUES (:player,
        :stat,
        :module,
        GREATEST((SELECT defaultValue FROM stats WHERE name = :stat AND module = :module), :score))
ON DUPLICATE KEY
    UPDATE score = GREATEST(score, :score);
-- #                }
-- #                {lowest
-- #                    :player string
-- #                    :stat string
-- #                    :module string
-- #                    :score string
INSERT INTO data(player,
                 stat,
                 module,
                 score)
VALUES (:player,
        :stat,
        :module,
        :score)
ON DUPLICATE KEY
    UPDATE score = LEAST(score, :score);
-- #                }
-- #            }
-- #        }
-- #    }
-- #    {get
-- #        {module
-- #            {displayName
-- #                :module string
SELECT displayName
FROM modules
WHERE name = :module;
-- #            }
-- #            {visible
-- #                :module string
SELECT visible
FROM modules
WHERE name = :module;
-- #            }
-- #            {data
-- #                :module string
SELECT displayName,
       visible
FROM modules
WHERE name = :module;
-- #            }
-- #            {all
SELECT name,
       displayName,
       visible
FROM modules;
-- #            }
-- #        }
-- #        {stat
-- #            {module
-- #                :stat string
-- #                :module string
SELECT module
FROM stats
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {type
-- #                :stat string
-- #                :module string
SELECT type
FROM stats
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {displayType
-- #                :stat string
-- #                :module string
SELECT displayType
FROM stats
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {default
-- #                :stat string
-- #                :module string
SELECT default
FROM stats
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {displayName
-- #                :stat string
-- #                :module string
SELECT displayName
FROM stats
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {visible
-- #                :stat string
-- #                :module string
SELECT visible
FROM stats
WHERE name = :stat
  AND module = :module;
-- #            }
-- #            {data
-- #                :stat name
SELECT module,
       type,
       defaultValue,
       displayName,
       visible
FROM stats
WHERE name = :stat;
-- #            }
-- #            {all
SELECT name,
       module,
       type,
       displayType,
       defaultValue,
       displayName,
       visible
FROM stats
ORDER BY position;
-- #            }
-- #        }
-- #        {data
-- #            {score
-- #                {of
-- #                    :stat string
-- #                    :module string
-- #                    :player string
SELECT score
FROM data
WHERE stat = :stat
  AND module = :module
  AND player = :player;
-- #                }
-- #                {all
-- #                    :stat string
-- #                    :module string
SELECT player,
       score
FROM data
WHERE stat = :stat
  AND module = :module;
-- #                }
-- #            }
-- #            {all
SELECT player,
       stat,
       module,
       score
FROM data;
-- #            }
-- #        }
-- #    }
-- #    {remove
-- #        {data
-- #            {score
-- #                :stat string
-- #                :module string
-- #                :player string
DELETE
FROM data
WHERE stat = :stat
  AND module = :module
  AND player = :player;
-- #            }
-- #            {player
-- #                :player string
DELETE
FROM data
WHERE player = :player;
-- #            }
-- #            {stat
-- #                :stat string
-- #                :module string
DELETE
FROM data
WHERE stat = :stat
  AND module = :module;
-- #            }
-- #        }
-- #    }
-- #}