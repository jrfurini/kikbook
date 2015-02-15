<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

ERROR - 2013-07-17 00:33:44 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 00:34:47 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 00:46:27 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 01:01:03 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 01:17:55 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 01:31:24 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 01:33:27 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 01:47:20 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 01:57:22 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 02:09:22 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 02:12:55 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 02:21:58 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 02:22:30 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 03:31:22 --> 404 Page Not Found --> regra/campeonato
ERROR - 2013-07-17 03:31:23 --> 404 ERROR (início)
ERROR - 2013-07-17 03:31:23 -->      Heading: 404 Page Not Found
ERROR - 2013-07-17 03:31:23 -->          Msg: <p> The page 'regra/campeonato' was not found.</p>
ERROR - 2013-07-17 03:31:23 --> 404 ERROR (fim)
ERROR - 2013-07-17 03:34:36 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 04:37:00 --> 404 Page Not Found --> regra/campeonato
ERROR - 2013-07-17 04:37:00 --> 404 ERROR (início)
ERROR - 2013-07-17 04:37:00 -->      Heading: 404 Page Not Found
ERROR - 2013-07-17 04:37:00 -->          Msg: <p> The page 'regra/campeonato' was not found.</p>
ERROR - 2013-07-17 04:37:00 --> 404 ERROR (fim)
ERROR - 2013-07-17 06:25:54 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 06:32:44 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 07:05:24 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 08:40:15 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 08:53:50 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 09:03:42 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 09:04:13 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 09:30:47 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '( jogo.rodada_fase_id = 308) ) 
ORDER BY jogo.data_hora, concat( eqp_casa.nome, ' at line 10
ERROR - 2013-07-17 09:30:48 --> DB ERROR (início)
ERROR - 2013-07-17 09:30:48 -->      Heading: A Database Error Occurred
ERROR - 2013-07-17 09:30:48 -->          Msg: <p> Error Number: 1064</p><p>You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '( jogo.rodada_fase_id = 308) ) 
ORDER BY jogo.data_hora, concat( eqp_casa.nome, ' at line 10</p><p>SELECT COUNT(*) AS numrows
FROM (jogo)
JOIN rodada_fase AS rod ON rod.id  = jogo.rodada_fase_id
JOIN campeonato_versao_imagem AS verimg ON verimg.campeonato_versao_id  = rod.campeonato_versao_id
LEFT JOIN equipe AS eqp_casa ON eqp_casa.id = jogo.equipe_id_casa
LEFT JOIN equipe AS eqp_vis ON eqp_vis.id  = jogo.equipe_id_visitante
LEFT JOIN equipe_imagem AS eqpimg_casa ON eqpimg_casa.equipe_id = eqp_casa.id
LEFT JOIN equipe_imagem AS eqpimg_vis ON eqpimg_vis.equipe_id = eqp_vis.id
LEFT JOIN arena AS arena ON arena.id = jogo.arena_id
WHERE ( ( upper( jogo.cod ) like '%SPORT%' or upper( jogo.titulo_casa ) like '%SPORT%' or upper( jogo.titulo_visitante ) like '%SPORT%' or upper( jogo.id_externo ) like '%SPORT%' or upper( jogo.renda_moeda ) like '%SPORT%' or upper( concat( eqp_casa.nome, ' ', cast( IFNULL( jogo.resultado_casa, ' ' ) AS CHAR ), ' X ', cast( IFNULL( jogo.resultado_visitante, ' ' ) AS CHAR ), ' ', eqp_vis.nome, ' (Rodada ', rod.cod, ') ', ' ', date_format( jogo.data_hora, '%a %e/%m/%Y %H:%i' ), ' ', case when IFNULL( jogo.resultado_visitante, '-1' ) IS NULL then 'Em aberto' else 'Realizado' end ) ) like '%SPORT%') ( jogo.rodada_fase_id = 308) ) 
ORDER BY jogo.data_hora, concat( eqp_casa.nome, ' X ', eqp_vis.nome, ' (', jogo.cod, ')' )</p><p>Filename: /var/www/kikbook/application/core/JX_Model.php</p><p>Line Number: 1899</p>
ERROR - 2013-07-17 09:30:48 --> DB ERROR (fim)
ERROR - 2013-07-17 10:39:03 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 10:52:27 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 10:55:09 --> 404 Page Not Found --> regra/campeonato
ERROR - 2013-07-17 10:55:10 --> 404 ERROR (início)
ERROR - 2013-07-17 10:55:10 -->      Heading: 404 Page Not Found
ERROR - 2013-07-17 10:55:10 -->          Msg: <p> The page 'regra/campeonato' was not found.</p>
ERROR - 2013-07-17 10:55:10 --> 404 ERROR (fim)
ERROR - 2013-07-17 10:55:11 --> 404 Page Not Found --> regra/campeonato
ERROR - 2013-07-17 10:55:11 --> 404 ERROR (início)
ERROR - 2013-07-17 10:55:11 -->      Heading: 404 Page Not Found
ERROR - 2013-07-17 10:55:11 -->          Msg: <p> The page 'regra/campeonato' was not found.</p>
ERROR - 2013-07-17 10:55:11 --> 404 ERROR (fim)
ERROR - 2013-07-17 11:10:58 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 11:26:21 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 11:43:22 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 11:54:20 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 11:57:22 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 12:24:25 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 12:25:51 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 12:27:46 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 13:21:27 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 14:38:44 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 15:11:07 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 15:25:56 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 15:26:53 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 15:27:18 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 15:30:03 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 15:45:17 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 16:49:14 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 16:49:19 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 16:56:46 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 17:00:14 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 17:07:17 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 17:09:49 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 17:38:32 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 17:38:33 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 18:21:54 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 18:53:51 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 18:55:30 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 19:34:01 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 19:34:03 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 20:09:28 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 20:32:38 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 20:39:36 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 20:46:47 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 21:19:21 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 21:23:33 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 21:25:57 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 21:43:54 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 22:27:38 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 22:38:26 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 23:02:38 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 23:04:22 --> FACEBOOK UPDATE.
ERROR - 2013-07-17 23:44:06 --> FACEBOOK UPDATE.
