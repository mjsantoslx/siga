INSERT INTO `tipos_evento` (`Designacao`) SELECT 'Admissão' WHERE NOT EXISTS (SELECT 1 FROM `tipos_evento` WHERE `Designacao`='Admissão');
