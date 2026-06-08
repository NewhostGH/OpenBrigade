# Legacy → Laravel mapping

Maps every file under `archive/legacy_app/` to its counterpart in the new Laravel
implementation. The legacy app is a flat procedural PHP codebase (one script per
page/action); the new app is an MVC Laravel app under `app/`, `routes/`,
`resources/views/`.

**Legend**
- A path under `app/...` / `resources/...` means the legacy file's behaviour is (at
  least partly) ported there.
- **WIP** = not yet ported. The feature still lives only in legacy (often reachable
  through `routes/web_legacy_bridge.php` / `LegacyBridgeController`), or has no new
  equivalent yet. Behaviour is unchanged from the legacy version.

**Scope** — per request, this excludes `webfonts/`, `user-data/`, `sql/`,
`scripts/`; for `lib/` only the module names are listed. Static `images/` are mapped
at folder granularity (they are binary assets copied as-is).

> Helper grouping note: legacy `fonctions_*.php` were a shared procedural function
> library. Their logic is being dissolved into Eloquent models, controllers and
> form requests rather than ported 1:1; each is listed in its domain below.

---

## Authentication & session

| Legacy file | New implementation |
|---|---|
| `login.php` | `app/Http/Controllers/AuthController.php` + `resources/views/auth/login.blade.php` |
| `identification.php` | `AuthController.php` (login attempt) |
| `deconnexion.php` | `AuthController.php` (logout) |
| `change_password.php` | **WIP** |
| `save_password.php` | **WIP** |
| `lost_password.php` | **WIP** |
| `lost_session.php` | **WIP** |
| `send_id.php` | **WIP** |
| `charte.php` | **WIP** |
| `connected_users.php` | **WIP** (see `admin/monitoring`) |

## Dashboard / home

| Legacy file | New implementation |
|---|---|
| `index.php` | `app/Http/Controllers/DashboardController.php` + `resources/views/dashboard/` |
| `index_d.php` | `DashboardController.php` |
| `save_accueil.php` | **WIP** (widget layout persistence) |
| `myagenda.php` | **WIP** |
| `noscript.php` | **WIP** |
| `error.php` | **WIP** |
| `config.php` | N/A — replaced by Laravel `config/` + `.env` |
| `wizard.php` | **WIP** (first-run setup) |

## Events (événements)

| Legacy file | New implementation |
|---|---|
| `evenements.php` | `app/Http/Controllers/EvenementController.php` + `resources/views/evenement/index.blade.php` |
| `evenement_edit.php` | `EvenementController.php` (`create`/`edit`) + `evenement/form.blade.php` |
| `evenement_save.php` | `EvenementController.php` (`store`/`update`) |
| `evenement_detail.php` | `EvenementController.php` (`show`) + `evenement/show.blade.php` |
| `evenement_display.php` | `EvenementController.php` (`show`) |
| `evenement_modal.php` | `evenement/show.blade.php` |
| `evenement_choice.php` | `EvenementController.php` |
| `evenement_duplicate.php` | **WIP** |
| `evenement_inscription.php` | `EvenementController.php` (`participantStore`) |
| `evenement_info_participant.php` | `EvenementController.php` (`participantUpdate`) |
| `evenement_equipes.php` | `EvenementController.php` (`equipe*`) |
| `evenement_materiel_add.php` | `EvenementController.php` (`materielAttach`) |
| `evenement_vehicule_add.php` | `EvenementController.php` (`vehiculeAttach`) |
| `evenement_consommable.php` | `EvenementController.php` (consommables) |
| `evenement_consommable_add.php` | `EvenementController.php` |
| `evenement_add_renfort.php` | `EvenementController.php` (`renfortAttach`) |
| `evenement_multi_renforts.php` | `EvenementController.php` (`renfortAttach`) |
| `evenement_horaires.php` | `EvenementController.php` (horaires) |
| `evenement_garde.php` | `app/Http/Controllers/GardeController.php` |
| `evenement_ical.php` | `EvenementController.php` (`exportIcal`) |
| `evenement_list_xls.php` | `EvenementController.php` (`exportParticipants`) |
| `evenement_xls.php` | `EvenementController.php` (export) |
| `evenement_vehicule_xls.php` | **WIP** |
| `evenement_competences.php` | **WIP** |
| `evenement_diplome.php` | **WIP** |
| `evenement_options.php` | **WIP** |
| `evenement_option_choix.php` | **WIP** |
| `evenement_notify.php` | **WIP** |
| `evenement_rapport.php` | **WIP** |
| `evenement_trombinoscope.php` | **WIP** |
| `evenement_facturation.php` | **WIP** |
| `evenement_facturation_detail.php` | **WIP** |
| `evenement_facturation_num.php` | **WIP** |
| `evenement_tarif.php` | **WIP** |
| `evenement_tarif_formation.php` | **WIP** |
| `repo_events.php` | **WIP** |
| `bilan_participation.php` | **WIP** (see Statistiques) |

## Personnel / members

| Legacy file | New implementation |
|---|---|
| `personnel.php` | `app/Http/Controllers/PersonnelController.php` + `resources/views/personnel/index.blade.php` |
| `personnel_load.php` | `PersonnelController.php` (`show`) + `personnel/show.blade.php` |
| `membres.php` | `PersonnelController.php` (`index`) |
| `ins_personnel.php` | `PersonnelController.php` (`create`/`store`) + `personnel/form.blade.php` |
| `upd_personnel.php` | `PersonnelController.php` (`edit`/`update`) |
| `save_personnel.php` | `PersonnelController.php` (`store`/`update`) |
| `del_personnel.php` | `PersonnelController.php` (`destroy`) |
| `deletePompier.php` | `PersonnelController.php` (`destroy`) |
| `personnel_xls.php` | `PersonnelController.php` (`exportXls`) |
| `personnel_evenement_xls.php` | `PersonnelController.php` (`exportCsv`/`exportXls`) |
| `personnel_reunion_xls.php` | **WIP** |
| `save_info_adherent.php` | `PersonnelController.php` (`update`) |
| `upd_personnel_salarie.php` | **WIP** |
| `personnel_contact.php` | **WIP** |
| `personnel_maitre.php` | **WIP** |
| `personnel_tenues.php` | **WIP** |
| `save_personnel_tenues.php` | **WIP** |
| `personnel_preferences.php` | **WIP** |
| `save_preferences.php` | **WIP** |
| `personnel_formation.php` | **WIP** |
| `save_personnel_formation.php` | **WIP** |
| `del_personnel_formation.php` | **WIP** |
| `formations_xls.php` | **WIP** |
| `diplome_edit.php` | **WIP** |
| `qualifications.php` | `PersonnelController.php` (`*Qualification`) + `personnel/qualifications.blade.php` |
| `qualifications_xls.php` | **WIP** |
| `save_qualif.php` | `PersonnelController.php` (`storeQualification`) |
| `save_qualif2.php` | `PersonnelController.php` (`updateQualification`) |
| `listecontacts.php` | **WIP** |
| `listemails.php` | **WIP** |
| `search_personnel.php` | `PersonnelController.php` (`index` filters) |
| `search_personnel_result.php` | `PersonnelController.php` |
| `livesearch.php` | **WIP** |
| `user_info.php` | **WIP** |
| `specific_info.php` | **WIP** |
| `homonymes_manage.php` | **WIP** |
| `homonymes_modal.php` | **WIP** |
| `vcard.php` | **WIP** |
| `vcard_class.php` | **WIP** |
| `trombinoscope.php` | **WIP** |
| `organigramme.php` | `app/Http/Controllers/OrganisationController.php` |

## Habilitations (access rights)

| Legacy file | New implementation |
|---|---|
| `habilitations.php` | `app/Http/Controllers/HabilitationController.php` + `resources/views/admin/habilitations/index.blade.php` |
| `save_habilitations.php` | `HabilitationController.php` (`toggle`/`group*`) |
| `upd_habilitations.php` | `HabilitationController.php` |
| `habilitations_xls.php` | **WIP** |
| `hierarchie_competence.php` | **WIP** |
| `save_hierarchie_competence.php` | **WIP** |
| `upd_hierarchie_competence.php` | **WIP** |

## Organisation (sections / teams / groups / posts)

| Legacy file | New implementation |
|---|---|
| `section.php` | `app/Http/Controllers/OrganisationController.php` + `resources/views/organisation/index.blade.php` |
| `ins_section.php` | `OrganisationController.php` |
| `upd_section.php` | `OrganisationController.php` |
| `save_section.php` | `OrganisationController.php` |
| `del_section.php` | `OrganisationController.php` |
| `section_stop.php` | **WIP** |
| `radier_section.php` | **WIP** |
| `rebuild_section_flat.php` | **WIP** |
| `choice_section_order.php` | **WIP** |
| `equipe.php` | `OrganisationController.php` |
| `ins_groupe.php` | `OrganisationController.php` |
| `del_groupe.php` | `OrganisationController.php` |
| `upd_equipe.php` | `OrganisationController.php` |
| `save_equipe.php` | `OrganisationController.php` |
| `del_equipe.php` | `OrganisationController.php` |
| `poste.php` | `OrganisationController.php` |
| `ins_poste.php` | `OrganisationController.php` |
| `upd_poste.php` | `OrganisationController.php` |
| `save_poste.php` | `OrganisationController.php` |
| `del_poste.php` | `OrganisationController.php` |
| `upd_responsable.php` | **WIP** |

## Vehicles

| Legacy file | New implementation |
|---|---|
| `vehicule.php` | `app/Http/Controllers/VehiculeController.php` + `resources/views/vehicule/index.blade.php` |
| `vehicule_load.php` | `VehiculeController.php` (`show`) + `vehicule/show.blade.php` |
| `ins_vehicule.php` | `VehiculeController.php` (`create`/`store`) + `vehicule/form.blade.php` |
| `upd_vehicule.php` | `VehiculeController.php` (`edit`/`update`) |
| `save_vehicule.php` | `VehiculeController.php` (`store`/`update`) |
| `del_vehicule.php` | `VehiculeController.php` (`destroy`) |
| `vehicule_xls.php` | **WIP** |
| `paramfnv.php` | **WIP** (vehicle functions/options) |
| `paramfnv_edit.php` | **WIP** |
| `paramfnv_save.php` | **WIP** |
| `type_vehicule.php` | `ParametrageController.php` (`typeVehicule*`) + `admin/parametrage/type-vehicule.blade.php` |
| `save_type_vehicule.php` | `ParametrageController.php` (`typeVehiculeStore`) |
| `upd_type_vehicule.php` | `ParametrageController.php` (`typeVehiculeUpdate`) |
| `del_type_vehicule.php` | `ParametrageController.php` (`typeVehiculeDestroy`) |

## Equipment (matériel)

| Legacy file | New implementation |
|---|---|
| `materiel.php` | `app/Http/Controllers/MaterielController.php` + `resources/views/materiel/index.blade.php` |
| `materiel_load.php` | `MaterielController.php` |
| `ins_materiel.php` | `MaterielController.php` |
| `upd_materiel.php` | `MaterielController.php` |
| `upd_materiel_selector.php` | `MaterielController.php` |
| `save_materiel.php` | `MaterielController.php` |
| `del_materiel.php` | `MaterielController.php` |
| `materiel_embarquer.php` | **WIP** |
| `materiel_xls.php` | **WIP** |
| `type_materiel.php` | `ParametrageController.php` (`typeMateriel*`) + `admin/parametrage/type-materiel.blade.php` |
| `ins_type_materiel.php` | `ParametrageController.php` (`typeMaterielStore`) |
| `save_type_materiel.php` | `ParametrageController.php` (`typeMaterielStore`) |
| `upd_type_materiel.php` | `ParametrageController.php` (`typeMaterielUpdate`) |
| `del_type_materiel.php` | `ParametrageController.php` (`typeMaterielDestroy`) |

## Consumables

| Legacy file | New implementation |
|---|---|
| `consommable.php` | `app/Http/Controllers/ConsommableController.php` + `resources/views/consommable/index.blade.php` |
| `consommable_load.php` | `ConsommableController.php` |
| `save_consommable.php` | `ConsommableController.php` |
| `upd_consommable.php` | `ConsommableController.php` |
| `del_consommable.php` | `ConsommableController.php` |
| `consommable_xls.php` | **WIP** |
| `type_consommable.php` | `ParametrageController.php` (`typeConsommable*`) + `admin/parametrage/type-consommable.blade.php` |
| `save_type_consommable.php` | `ParametrageController.php` (`typeConsommableStore`) |
| `upd_type_consommable.php` | `ParametrageController.php` (`typeConsommableUpdate`) |
| `del_type_consommable.php` | `ParametrageController.php` (`typeConsommableDestroy`) |
| `edit_categorie_consommable.php` | **WIP** |
| `save_edit_categorie_consommable.php` | **WIP** |
| `del_edit_categorie_consommable.php` | **WIP** |

## Dues / finance (cotisations)

| Legacy file | New implementation |
|---|---|
| `cotisations.php` | `app/Http/Controllers/CotisationController.php` + `resources/views/cotisations/index.blade.php` |
| `cotisation_edit.php` | `CotisationController.php` (`batchSave`) |
| `save_cotisations.php` | `CotisationController.php` (`batchSave`) |
| `cotisations_xls.php` | `CotisationController.php` (`export`) |
| `cotisations_extract.php` | `CotisationController.php` (`export`) |
| `report_cotisations.php` | **WIP** |
| `prelevements.php` | `CotisationController.php` (`prelevements`) + `cotisations/prelevements.blade.php` |
| `save_prelevements.php` | `CotisationController.php` (`savePrelevements`) |
| `config_prelevements.php` | **WIP** |
| `virements.php` | `CotisationController.php` (`virements`) + `cotisations/virements.blade.php` |
| `virements_extract.php` | **WIP** |
| `element_facturable.php` | **WIP** |
| `save_element_facturable.php` | **WIP** |
| `del_element_facturable.php` | **WIP** |
| `save_detail_facture.php` | **WIP** |
| `note_frais_edit.php` | **WIP** |
| `note_frais_save.php` | **WIP** |
| `edit_categorie.php` | **WIP** |
| `save_edit_categorie.php` | **WIP** |
| `del_edit_categorie.php` | **WIP** |
| `paramfn.php` | **WIP** (billable function params) |
| `paramfn_edit.php` | **WIP** |
| `paramfn_save.php` | **WIP** |

## Grades

| Legacy file | New implementation |
|---|---|
| `grades_load.php` | `ParametrageController.php` (`gradeIndex`) + `admin/parametrage/grade.blade.php` |
| `edit_grades.php` | `ParametrageController.php` (`gradeIndex`) |
| `save_grades.php` | `ParametrageController.php` |
| `upd_grades.php` | `ParametrageController.php` |
| `del_grade.php` | `ParametrageController.php` |
| `configuration_icone_grade.php` | `ParametrageController.php` (`gradeIconUpload`/`gradeIconDestroy`) |
| `edit_categorie_grades.php` | **WIP** |
| `save_edit_categorie_grades.php` | **WIP** |
| `del_categorie_grade.php` | **WIP** |

## Duty / on-call (gardes, astreintes, piquets)

| Legacy file | New implementation |
|---|---|
| `astreintes.php` | `app/Http/Controllers/GardeController.php` (`astreintes`) + `resources/views/garde/astreintes.blade.php` |
| `astreinte_edit.php` | `GardeController.php` |
| `astreinte_save.php` | `GardeController.php` |
| `astreintes_updates.php` | `GardeController.php` |
| `feuille_garde.php` | `GardeController.php` (`index`) + `garde/index.blade.php` |
| `save_garde.php` | `GardeController.php` |
| `tableau_garde.php` | **WIP** |
| `tableau_garde_create.php` | **WIP** |
| `tableau_garde_status.php` | **WIP** |
| `tableau_garde_xls.php` | **WIP** |
| `auto_garde.php` | **WIP** |
| `automaticPiquet.php` | **WIP** |
| `save_piquet.php` | **WIP** |
| `type_garde.php` | **WIP** |
| `save_type_garde.php` | **WIP** |
| `del_type_garde.php` | **WIP** |
| `demande_renfort.php` | **WIP** |

## Availability / unavailability / replacements

| Legacy file | New implementation |
|---|---|
| `dispo.php` | `app/Http/Controllers/DispoController.php` + `resources/views/dispo/index.blade.php` |
| `save_dispo.php` | `DispoController.php` |
| `indispo.php` | `app/Http/Controllers/IndispoController.php` + `resources/views/indispo/index.blade.php` |
| `indispo_choice.php` | `IndispoController.php` |
| `indispo_display.php` | `IndispoController.php` |
| `indispo_save.php` | `IndispoController.php` |
| `indispo_status.php` | `IndispoController.php` |
| `indispo_list_xls.php` | **WIP** |
| `remplacements.php` | `app/Http/Controllers/RemplacementController.php` + `resources/views/remplacement/index.blade.php` |
| `remplacement_edit.php` | `RemplacementController.php` |
| `intervention_edit.php` | **WIP** |
| `repos_saisie.php` | **WIP** |
| `repos_save.php` | **WIP** |

## Planning / calendar

| Legacy file | New implementation |
|---|---|
| `planning.php` | `app/Http/Controllers/PlanningController.php` + `resources/views/planning/index.blade.php` |
| `planning_xls.php` | **WIP** |
| `calendar.php` | `PlanningController.php` |

## Documents

| Legacy file | New implementation |
|---|---|
| `documents.php` | `app/Http/Controllers/DocumentController.php` + `resources/views/document/index.blade.php` |
| `document_modal.php` | `document/index.blade.php` |
| `save_documents.php` | `DocumentController.php` |
| `save_folder.php` | `DocumentController.php` |
| `upd_folder.php` | `DocumentController.php` |
| `delete_file.php` | `DocumentController.php` |
| `delete_event_file.php` | **WIP** |
| `upload.php` | `DocumentController.php` |
| `showfile.php` | `DocumentController.php` |
| `config_doc.php` | **WIP** |
| `observations_modal.php` | **WIP** |
| `document_folders` / `document_security` docs | see `archive/legacy_app/documentation/` below |

## Messaging / mail / SMS / alerts / chat

| Legacy file | New implementation |
|---|---|
| `message.php` | `app/Http/Controllers/MessageController.php` + `resources/views/message/index.blade.php` |
| `delete_message.php` | `MessageController.php` |
| `mail_create.php` | **WIP** |
| `mail_create_input.php` | **WIP** |
| `mail_send.php` | **WIP** |
| `mailer.php` | **WIP** |
| `mailto.php` | **WIP** |
| `destinataires.php` | **WIP** |
| `chat.php` | **WIP** |
| `chat_message.php` | **WIP** |
| `alerte_create.php` | **WIP** |
| `alerte_send.php` | **WIP** |
| `reminder.php` | **WIP** |
| `histo_sms.php` | **WIP** |
| `push_monitor.php` | **WIP** |
| `rss.php` | **WIP** |

## Geolocation / maps

| Legacy file | New implementation |
|---|---|
| `gps.php` | `app/Http/Controllers/GeolocalisationController.php` + `resources/views/personnel/geolocalisation.blade.php` |
| `gps_save.php` | `GeolocalisationController.php` |
| `gps_save2.php` | `GeolocalisationController.php` |
| `gmaps_personnel.php` | `GeolocalisationController.php` |
| `gmaps_evenement.php` | **WIP** |
| `geolocalize_all_persons.php` | `GeolocalisationController.php` |
| `localize.php` | **WIP** |
| `localize_me.php` | **WIP** |
| `localize_send.php` | **WIP** |
| `map.php` | **WIP** |
| `jvectormap.php` | **WIP** |
| `departement.php` | **WIP** |
| `zipcode.php` | **WIP** |
| `buildzipcode.php` | **WIP** |

## Statistics / reporting / audit

| Legacy file | New implementation |
|---|---|
| `bilans.php` | `app/Http/Controllers/StatistiqueController.php` + `resources/views/statistique/index.blade.php` |
| `bilan_participation.php` | `StatistiqueController.php` |
| `delete_statistique.php` | `StatistiqueController.php` |
| `history.php` | **WIP** |
| `audit.php` | **WIP** |

## Company / configuration / settings

| Legacy file | New implementation |
|---|---|
| `configuration.php` | `app/Http/Controllers/AdminController.php` (`settings`) + `resources/views/admin/settings.blade.php` |
| `save_configuration.php` | `AdminController.php` (`saveSetting`/`uploadSetting`/`deleteSetting`) |
| `configuration_db.php` | `MaintenanceController.php` |
| `configuration_theme.php` | **WIP** |
| `parametrage.php` | `app/Http/Controllers/ParametrageController.php` (`index`) + `admin/parametrage/index.blade.php` |
| `company.php` | `app/Http/Controllers/CompanyController.php` + `resources/views/company/index.blade.php` |
| `save_company.php` | `CompanyController.php` |
| `upd_company.php` | `CompanyController.php` |
| `ins_company.php` | `CompanyController.php` |
| `del_company.php` | `CompanyController.php` |
| `upd_company_role.php` | **WIP** |
| `company_xls.php` | **WIP** |
| `menu_status_set.php` | `app/Http/Controllers/ShortcutController.php` |

## Backup / restore / maintenance / upgrade

| Legacy file | New implementation |
|---|---|
| `backup.php` | `app/Http/Controllers/BackupController.php` + `resources/views/admin/backup/index.blade.php` |
| `restore.php` | `BackupController.php` (`restore`) |
| `fonctions_backup.php` | `BackupController.php` |
| `database_maintenance.php` | `app/Http/Controllers/MaintenanceController.php` + `resources/views/admin/maintenance/index.blade.php` |
| `upgrade.php` | `MaintenanceController.php` (migration status) |
| `update_app.php` | **WIP** |
| `update_page.php` | **WIP** |
| `buildsql.php` | **WIP** |
| `decrypt.php` | **WIP** |
| `import_api.php` | **WIP** |
| `phpinfo.php` | **WIP** (see `admin/maintenance` system info) |
| `browscap.php` | **WIP** |
| `debug_data.php` | **WIP** |

## Add-ons / packages

| Legacy file | New implementation |
|---|---|
| `addons.php` | **WIP** |
| `addons_save.php` | **WIP** |
| `install_addon.php` | **WIP** |
| `download_addon.php` | **WIP** |
| `download_module.php` | **WIP** |
| `download_package.php` | **WIP** |

## PDF generation

| Legacy file | New implementation |
|---|---|
| `pdf.php` | **WIP** |
| `pdf_asa.php` | **WIP** |
| `pdf_attestation_fiscale.php` | **WIP** |
| `pdf_attestation_formation.php` | **WIP** |
| `pdf_bilans.php` | **WIP** |
| `pdf_bulletin.php` | **WIP** |
| `pdf_carte_adherent.php` | **WIP** |
| `pdf_courrier_nouvel_adherent.php` | **WIP** |
| `pdf_diplome.php` | **WIP** |
| `pdf_document.php` | **WIP** |
| `pdf_livret.php` | **WIP** |
| `export_badges.php` | **WIP** |

## Exports

| Legacy file | New implementation |
|---|---|
| `export.php` | **WIP** (per-module XLS/CSV exports exist on individual controllers) |
| `export-html.php` | **WIP** |
| `export-sql.php` | **WIP** |
| `export-sql-liste.php` | **WIP** |
| `export-tcd.php` | **WIP** |
| `export-txt.php` | **WIP** |
| `export-xls.php` | **WIP** |
| `iCalcreator.class.php` | `EvenementController.php` (`exportIcal`) |

## Emergency ops (DPS / SITAC / victims)

| Legacy file | New implementation |
|---|---|
| `dps.php` | **WIP** |
| `dps_calc.php` | **WIP** |
| `dps_save.php` | **WIP** |
| `sitac.php` | **WIP** |
| `sitac_options.php` | **WIP** |
| `sitac_save.php` | **WIP** |
| `victimes.php` | **WIP** |
| `liste_victimes.php` | **WIP** |
| `scan_victime.php` | **WIP** |
| `intervention_edit.php` | **WIP** |

## QR codes / scanning / misc utilities

| Legacy file | New implementation |
|---|---|
| `qrcode.php` | **WIP** |
| `qrcode_pic.php` | **WIP** |
| `cav_edit.php` | **WIP** |
| `paginator.class.php` | N/A — replaced by Laravel pagination |

## Shared procedural helper library (`fonctions_*`)

These are being dissolved into Eloquent models, controllers and form requests
rather than ported as standalone files.

| Legacy file | New implementation |
|---|---|
| `fonctions.php` | Distributed across `app/Models/*` and controllers |
| `fonctions_sql.php` | N/A — replaced by Eloquent / query builder |
| `fonctions_parameters.php` | `ParametrageController.php` / `AdminController.php` |
| `fonctions_menu.php` | `resources/views/layout/sidebar.blade.php` + `ShortcutController.php` |
| `fonctions_infos.php` | `DashboardController.php` (widgets) |
| `fonctions_chart.php` | `StatistiqueController.php` |
| `fonctions_gardes.php` | `GardeController.php` |
| `fonctions_gardes_auto.php` | **WIP** |
| `fonctions_documents.php` | `DocumentController.php` |
| `fonctions_map.php` | `GeolocalisationController.php` |
| `fonctions_bank.php` | `CotisationController.php` (partial) |
| `fonctions_import.php` | **WIP** |
| `fonctions_unzip.php` | **WIP** |
| `fonctions_mail.php` | **WIP** |
| `fonctions_sms.php` | **WIP** |
| `fonctions_dps.php` | **WIP** |
| `fonctions_specific.php` | **WIP** |

---

## `api/` (REST import/export)

All endpoints are **WIP** — no equivalent under `routes/api.php` yet.

| Legacy file | New implementation |
|---|---|
| `api/index.php` | **WIP** |
| `api/export/index.php` | **WIP** |
| `api/export/search.php` | **WIP** |
| `api/export/test/index.php` | **WIP** |
| `api/export/test/search_people.php` | **WIP** |
| `api/import/index.php` | **WIP** |
| `api/import/event.php` | **WIP** |
| `api/import/people.php` | **WIP** |
| `api/import/test/index.php` | **WIP** |
| `api/import/test/event.php` | **WIP** |
| `api/import/test/insert_people.php` | **WIP** |
| `api/import/test/update_people.php` | **WIP** |

## `conf/`

| Legacy file | New implementation |
|---|---|
| `conf/index.php` | N/A — replaced by Laravel `config/` + `.env` |
| `conf/optional.php.template` | N/A — replaced by `.env.example` |

## `documentation/`

Design notes; not application code. Kept for reference.

| Legacy file | New implementation |
|---|---|
| `documentation/db-info_document_folders.md` | Reference (informs `DocumentController.php` schema) |
| `documentation/db-info_document_security.md` | Reference |
| `documentation/db_modify_document_security_options.md` | Reference |

## `lib/` (third-party modules — names only)

Replaced by Composer packages where a Laravel equivalent exists; otherwise **WIP**.

| Legacy module | New implementation |
|---|---|
| `lib/PBKDF2/` | N/A — replaced by Laravel Hash/bcrypt |
| `lib/PHPMailer/` | N/A — replaced by Laravel Mail (when mail ported) |
| `lib/SMSGatewayMe/` | **WIP** |
| `lib/fpdf/` | **WIP** (PDF generation not ported) |
| `lib/phpqrcode/` | **WIP** |
| `lib/vendor/` | N/A — replaced by Composer `vendor/` |
| `lib/index.php` | N/A |

---

## Front-end assets

### `css/`
The legacy Bootstrap/jQuery stylesheets are replaced by the token-based CSS under
`resources/css/` (compiled via Vite). Vendor CSS (Bootstrap, datepicker, select,
table, toggle, croppie, jvectormap) is **WIP** / dropped in favour of the new
component system.

| Legacy file | New implementation |
|---|---|
| `css/main.css` | `resources/css/base.css`, `components.css`, `layout.css`, `variables.css` |
| `css/login.css` | `resources/css/login.css` |
| `css/print.css`, `css/export-print.css` | **WIP** |
| `css/Chart.css` | **WIP** |
| `css/imginput.css` | `resources/css/ob-avatar.css` |
| `css/all.css`, `css/css.php`, `css/index.php` | N/A — replaced by Vite build |
| `css/bootstrap*.css`, `css/croppie.css`, `css/jquery-jvectormap-2.0.5.css` | **WIP** (vendor) |

### `js/`
Legacy jQuery page scripts are replaced by per-page ES modules under
`resources/js/` (Vite). Vendor libraries (jQuery, Bootstrap bundle, Chart, moment,
fullcalendar, tablesorter, tinymce, tokeninput, jvectormap, croppie, ddslick,
swal, etc.) are **WIP** / being dropped.

| Legacy file(s) | New implementation |
|---|---|
| `js/personnel.js`, `js/personnel_liste.js` | `resources/js/ob-personnel-index.js` |
| `js/save_personnel.js` | `resources/js/ob-personnel-form.js` |
| `js/evenement*.js` | `resources/js/ob-evenement-form.js`, `ob-evenement-show.js` |
| `js/cotisations.js` | `resources/js/ob-cotisations-index.js` |
| `js/vehicule.js`, `js/upd_vehicule.js` | `resources/js/ob-vehicule-form.js` |
| `js/gps.js` | `resources/js/ob-geolocalisation.js` |
| `js/login-general.js` | `resources/js/ob-auth-login.js` |
| `js/theme.js` | `resources/js/ob-sidebar.js`, `ob-shortcuts.js` |
| `js/all.js`, `js/checkForm.js`, `js/dateFunctions.js`, etc. | `resources/js/app.js` (shared helpers) |
| Other page scripts (`consommable.js`, `materiel.js`, `dispo.js`, `indispo.js`, `planning.js`, `section.js`, `equipe.js`, `poste.js`, `qualifications.js`, `documents.js`, `chat.js`, `habilitations.js`, `tableau_garde.js`, `feuille_garde.js`, `remplacement*.js`, `note_de_frais.js`, `sitac`/`victimes`/`scanner`, etc.) | **WIP** |
| Vendor: `jquery*.js`, `bootstrap*.js`, `Chart.bundle.min.js`, `moment-with-locales.min.js`, `js/fullcalendar/`, `js/tablesorter/`, `js/tinymce/`, `js/tokeninput/`, `js/columnFilters/`, `js/scanner/` | **WIP** (vendor; mostly dropped) |
| `js/color.php`, `js/index.php` | N/A — replaced by Vite build |

### `images/`
Static binary assets. UI chrome icons are superseded by the new component CSS;
domain/upload images are copied to `public/images/`.

| Legacy folder/file | New implementation |
|---|---|
| `images/` (root UI icons, gifs, logos) | `public/images/` / replaced by `resources/css/` component styling |
| `images/grades_sp/`, `images/grades_army/` | `public/images/` (grade icons, managed via `ParametrageController` grade icons) |
| `images/vehicules/` | `public/images/` |
| `images/evenements/` | `public/images/` |
| `images/gardes/` | `public/images/` |
| `images/flags/` | `public/images/` |
| `images/sitac/` | **WIP** (SITAC not ported) |
| `images/user-specific/` | `public/images/` (uploaded avatars / `storage`) |

---

## Excluded from this map (per request)

`webfonts/`, `user-data/`, `sql/`, `scripts/` — not enumerated.
