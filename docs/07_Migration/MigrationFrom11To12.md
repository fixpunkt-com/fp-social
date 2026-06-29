---
title: 11.0.x auf 12.x
---

### Änderungen in dieser Version:
* Alle durch den SocialServer abgedeckten Access-Klassen sind entfallen.
* Die alte Instagram-Klasse ist entfallen.
* Die Netzwerk- und Access-Klassen wurden mit den Models zusammengelegt.

### Migration

Bitte führen Sie folgenden Befehl in Ihrer Datenbank aus:
```mysql
UPDATE `tx_fpsocial_domain_model_access` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Access\\FpSocialServer' WHERE network = '\\Fixpunkt\\FpSocial\\Access\\FpSocialServer';
UPDATE `tx_fpsocial_domain_model_access` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Access\\Youtube' WHERE network = '\\Fixpunkt\\FpSocial\\Access\\Youtube';
UPDATE `tx_fpsocial_domain_model_access` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Access\\Wordpress' WHERE network = '\\Fixpunkt\\FpSocial\\Access\\Wordpress';

UPDATE `tx_fpsocial_domain_model_account` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Facebook' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Facebook';
UPDATE `tx_fpsocial_domain_model_account` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Instagram' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Instagram';
UPDATE `tx_fpsocial_domain_model_account` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\LinkedIn' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\LinkedIn';
UPDATE `tx_fpsocial_domain_model_account` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Twitter' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Twitter';
UPDATE `tx_fpsocial_domain_model_account` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Wordpress' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Wordpress';
UPDATE `tx_fpsocial_domain_model_account` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Youtube' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Youtube';

UPDATE `tx_fpsocial_domain_model_post` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Facebook' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Facebook';
UPDATE `tx_fpsocial_domain_model_post` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Instagram' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Instagram';
UPDATE `tx_fpsocial_domain_model_post` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\LinkedIn' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\LinkedIn';
UPDATE `tx_fpsocial_domain_model_post` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Twitter' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Twitter';
UPDATE `tx_fpsocial_domain_model_post` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Wordpress' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Wordpress';
UPDATE `tx_fpsocial_domain_model_post` SET network = 'Fixpunkt\\FpSocial\\Domain\\Model\\Account\\Youtube' WHERE network = '\\Fixpunkt\\FpSocial\\Networks\\Youtube';
```