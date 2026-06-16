# Legacy → Laravel mapping

Maps every file under `archive/legacy_app/` to its counterpart in the new Laravel
implementation. The legacy app is a flat procedural PHP codebase (one script per
page/action); the new app is an MVC Laravel app under `app/`, `routes/`,
`resources/views/`.

## Legend

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

| Legacy file           | New implementation                                                                 |
| --------------------- | ---------------------------------------------------------------------------------- |
| `login.php`           | `app/Http/Controllers/AuthController.php` + `resources/views/auth/login.blade.php` |
| `identification.php`  | `AuthController.php` (login attempt)                                               |
| `deconnexion.php`     | `AuthController.php` (logout)                                                      |
| `change_password.php` | `AccountController.php` (`authentification` / password change)                     |
| `save_password.php`   | `AccountController.php`                                                             |
| `lost_password.php`   | `PasswordResetController.php` (mailing not yet configured)                         |
| `lost_session.php`    | `PasswordResetController.php`                                                       |
| `send_id.php`         | `PasswordResetController.php`                                                       |
| `charte.php`          | `AuthController.php` (`charter`) + `resources/views/auth/charter.blade.php`        |
| `connected_users.php` | `AdminController.php` (`monitoring`)                                               |

## Dashboard / home

| Legacy file        | New implementation                                                                                                             |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------ |
| `index.php`        | `app/Http/Controllers/DashboardController.php` + `resources/views/dashboard/`                                                  |
| `index_d.php`      | `DashboardController.php`                                                                                                      |
| `save_accueil.php` | `DashboardController::saveLayout()` — `POST /dashboard/layout`, persists widget order/column per user in `ob_dashboard_layout` |
| `myagenda.php`     | `PlanningController.php` (personal agenda merged into the planning view)                                                       |
| `noscript.php`     | **WIP**                                                                                                                        |
| `error.php`        | **WIP**                                                                                                                        |
| `config.php`       | N/A — replaced by Laravel `config/` + `.env`                                                                                   |
| `wizard.php`       | **WIP** (first-run setup)                                                                                                      |
| `rebuild_section_flat.php` | **Retired** — `section_flat` table dropped; depth derived from `section.S_PARENT` tree at query time in `DashboardService` |

## Events (événements)

| Legacy file                        | New implementation                                                                           |
| ---------------------------------- | -------------------------------------------------------------------------------------------- |
| `evenements.php`                   | `app/Http/Controllers/EventController.php` + `resources/views/event/index.blade.php`         |
| `evenement_edit.php`               | `EventController.php` (`create`/`edit`) + `event/form.blade.php`                            |
| `evenement_save.php`               | `EventController.php` (`store`/`update`)                                                     |
| `evenement_detail.php`             | `EventController.php` (`show`) + `event/show.blade.php`                                      |
| `evenement_display.php`            | `EventController.php` (`show`)                                                               |
| `evenement_modal.php`              | `event/show.blade.php`                                                                       |
| `evenement_choice.php`             | `EventController.php`                                                                        |
| `evenement_duplicate.php`          | `EventController.php` (`duplicate`) — copy with date-shift, optional participants/vehicles  |
| `evenement_inscription.php`        | `EventController.php` (`participantStore`)                                                   |
| `evenement_info_participant.php`   | `EventController.php` (`participantUpdate`)                                                  |
| `evenement_equipes.php`            | `EventController.php` (`equipe*`)                                                            |
| `evenement_materiel_add.php`       | `EventController.php` (`materielAttach`)                                                     |
| `evenement_vehicule_add.php`       | `EventController.php` (`vehiculeAttach`)                                                     |
| `evenement_consommable.php`        | `EventController.php` (consommables)                                                         |
| `evenement_consommable_add.php`    | `EventController.php`                                                                        |
| `evenement_add_renfort.php`        | `EventController.php` (`renfortAttach`)                                                      |
| `evenement_multi_renforts.php`     | `EventController.php` (`renfortAttach`)                                                      |
| `evenement_horaires.php`           | `EventController.php` (horaires)                                                             |
| `evenement_garde.php`              | `app/Http/Controllers/DutyController.php`                                                    |
| `evenement_ical.php`               | `EventController.php` (`exportIcal`)                                                         |
| `evenement_list_xls.php`           | `EventController.php` (`exportParticipants`)                                                 |
| `evenement_xls.php`                | `EventController.php` (`exportListXls`/`exportListCsv`)                                      |
| `evenement_vehicule_xls.php`       | `EventController.php` (per-event vehicle XLS)                                                |
| `evenement_competences.php`        | `EventController.php` (`Postes requis` — required positions vs enrolled headcount)          |
| `evenement_diplome.php`            | **WIP**                                                                                      |
| `evenement_options.php`            | `EventController.php` (`optionGroupStore/Update/Destroy`, `optionStore/Update/Destroy`, `dropdownChoiceStore/Destroy`) + `event/show.blade.php` (Options d'inscription card + modals) |
| `evenement_option_choix.php`       | `EventController.php` (`participantChoicesSave`) + `event/show.blade.php` (`#choicesModal-{P_ID}` per participant)                                                                    |
| `evenement_log` (inline in `evenement_display.php`) | `EventController.php` (`logStore/Update/Destroy`) + `event/show.blade.php` (Main courante card + add/edit modals) |
| `evenement_notify.php`             | **WIP**                                                                                      |
| `evenement_rapport.php`            | **WIP**                                                                                      |
| `evenement_trombinoscope.php`      | `EventController.php` (`trombinoscope`) + `event/trombinoscope.blade.php`                   |
| `evenement_facturation.php`        | **WIP**                                                                                      |
| `evenement_facturation_detail.php` | **WIP**                                                                                      |
| `evenement_facturation_num.php`    | **WIP**                                                                                      |
| `evenement_tarif.php`              | **WIP**                                                                                      |
| `evenement_tarif_formation.php`    | **WIP**                                                                                      |
| `repo_events.php`                  | `StatisticsController.php` — bridge redirects to `statistics.index`                          |
| `bilan_participation.php`          | `StatisticsController.php` (bilan annuel, WIP parity)                                        |

## Personnel / members

| Legacy file                    | New implementation                                                                                   |
| ------------------------------ | ---------------------------------------------------------------------------------------------------- |
| `personnel.php`                | `app/Http/Controllers/PersonnelController.php` + `resources/views/personnel/index.blade.php`         |
| `personnel_load.php`           | `PersonnelController.php` (`show`) + `personnel/show.blade.php`                                      |
| `membres.php`                  | `PersonnelController.php` (`index`)                                                                  |
| `ins_personnel.php`            | `PersonnelController.php` (`create`/`store`) + `personnel/form.blade.php`                            |
| `upd_personnel.php`            | `PersonnelController.php` (`edit`/`update`)                                                          |
| `save_personnel.php`           | `PersonnelController.php` (`store`/`update`)                                                         |
| `del_personnel.php`            | `PersonnelController.php` (`destroy`)                                                                |
| `deletePompier.php`            | `PersonnelController.php` (`destroy`)                                                                |
| `personnel_xls.php`            | `PersonnelController.php` (`exportXls`)                                                              |
| `personnel_evenement_xls.php`  | `PersonnelController.php` (`exportCsv`/`exportXls`)                                                  |
| `personnel_reunion_xls.php`    | `PersonnelController.php` (per-member meeting participation XLS)                                      |
| `save_info_adherent.php`       | `PersonnelController.php` (`update`)                                                                 |
| `upd_personnel_salarie.php`    | `PersonnelController.php` (salarié contract/hours card)                                              |
| `personnel_contact.php`        | `PersonnelController.php` (`updateContacts` — contact handles card)                                  |
| `personnel_maitre.php`         | **WIP** (animaux module)                                                                             |
| `personnel_tenues.php`         | `PersonnelController.php` (`tenues`) + `personnel/tenues.blade.php`                                  |
| `save_personnel_tenues.php`    | `PersonnelController.php` (`tenues` save)                                                            |
| `personnel_preferences.php`    | `PersonnelController.php` (`preferences`) + `personnel/preferences.blade.php`                       |
| `save_preferences.php`         | `PersonnelController.php` (`preferences` save)                                                      |
| `personnel_formation.php`      | `PersonnelController.php` (`storeTraining`/`updateTraining`/`destroyTraining`) + `personnel/show.blade.php` (Formations card + modals) |
| `save_personnel_formation.php` | `PersonnelController.php` (`storeTraining`/`updateTraining`)                                                                           |
| `del_personnel_formation.php`  | `PersonnelController.php` (`destroyTraining`)                                                                                          |
| `formations_xls.php`           | `PersonnelController::exportFormationsXls()` — GET `/personnel/{id}/export/formations`, XLS button in Formations card                  |
| `diplome_edit.php`             | **WIP** (complex PDF field-positioning admin screen)                                                                                   |
| `qualifications.php`           | `PersonnelController.php` (`*Qualification`) + `personnel/qualifications.blade.php`                  |
| `qualifications_xls.php`       | `PersonnelController.php` (qualifications XLS/CSV, section-scoped)                                    |
| `save_qualif.php`              | `PersonnelController.php` (`storeQualification`)                                                     |
| `save_qualif2.php`             | `PersonnelController.php` (`updateQualification`)                                                    |
| `listecontacts.php`            | `PersonnelController.php` (contacts.csv bulk export)                                                 |
| `listemails.php`               | `PersonnelController.php` (emails.txt bulk export)                                                   |
| `search_personnel.php`         | `PersonnelController.php` (`index` filters)                                                          |
| `search_personnel_result.php`  | `PersonnelController.php`                                                                            |
| `livesearch.php`               | **WIP**                                                                                              |
| `user_info.php`                | **WIP**                                                                                              |
| `specific_info.php`            | **WIP** (custom member fields)                                                                       |
| `homonymes_manage.php`         | `PersonnelController.php` (`merge` — homonym detection + merge tool)                                 |
| `homonymes_modal.php`          | `personnel/merge.blade.php`                                                                          |
| `vcard.php`                    | `PersonnelController.php` (`exportVcard`) + `app/Services/PersonnelExportService.php` (`buildVcard`) |
| `vcard_class.php`              | `PersonnelExportService.php` (Sabre VObject)                                                         |
| `trombinoscope.php`            | `PersonnelController.php` (`trombinoscope`)                                                          |
| `organigramme.php`             | `app/Http/Controllers/OrganizationController.php`                                                    |

## Habilitations (access rights)

| Legacy file                      | New implementation                                                                                                                                             |
| -------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `habilitations.php`              | `app/Http/Controllers/PermissionController.php` + `resources/views/admin/permissions/index.blade.php` + `app/Services/PermissionResolver.php` (resolution); "Mes droits" preview in `MyPermissionsController.php` |
| `save_habilitations.php`         | `PermissionController.php` (`setGrant`/`setUserGrant`/`toggleCeiling`/`group*`)                                                                                |
| `upd_habilitations.php`          | `PermissionController.php`                                                                                                                                     |
| `habilitations_xls.php`          | `PermissionController.php` (`exportGroup` — per group/role member export XLS/CSV)                                                                              |
| `hierarchie_competence.php`      | **WIP**                                                                                                                                                        |
| `save_hierarchie_competence.php` | **WIP**                                                                                                                                                        |
| `upd_hierarchie_competence.php`  | **WIP**                                                                                                                                                        |

## Organisation (sections / teams / groups / posts)

| Legacy file                | New implementation                                                                                 |
| -------------------------- | -------------------------------------------------------------------------------------------------- |
| `section.php`              | `app/Http/Controllers/OrganizationController.php` + `resources/views/organization/index.blade.php` |
| `ins_section.php`          | `OrganizationController.php` (`storeSection`)                                                      |
| `upd_section.php`          | `OrganizationController.php` (`updateSection`)                                                     |
| `save_section.php`         | `OrganizationController.php`                                                                       |
| `del_section.php`          | `OrganizationController.php` (`destroySection`)                                                    |
| `section_stop.php`         | **WIP**                                                                                            |
| `radier_section.php`       | **WIP**                                                                                            |
| `rebuild_section_flat.php` | **WIP**                                                                                            |
| `choice_section_order.php` | **WIP**                                                                                            |
| `equipe.php`               | `ReferenceController.php` (`team*`) + `admin/references/team.blade.php`                            |
| `ins_groupe.php`           | `ReferenceController.php`                                                                          |
| `del_groupe.php`           | `ReferenceController.php`                                                                          |
| `upd_equipe.php`           | `ReferenceController.php` (`teamUpdate`)                                                           |
| `save_equipe.php`          | `ReferenceController.php` (`teamStore`)                                                            |
| `del_equipe.php`           | `ReferenceController.php` (`teamDestroy`)                                                          |
| `poste.php`                | `ReferenceController.php` (`position*`) + `admin/references/position.blade.php`                    |
| `ins_poste.php`            | `ReferenceController.php`                                                                          |
| `upd_poste.php`            | `ReferenceController.php` (`positionUpdate`)                                                       |
| `save_poste.php`           | `ReferenceController.php` (`positionStore`)                                                        |
| `del_poste.php`            | `ReferenceController.php` (`positionDestroy`)                                                      |
| `upd_responsable.php`      | **WIP**                                                                                            |

## Vehicles

| Legacy file              | New implementation                                                                          |
| ------------------------ | ------------------------------------------------------------------------------------------- |
| `vehicule.php`           | `app/Http/Controllers/VehicleController.php` + `resources/views/vehicle/index.blade.php`    |
| `vehicule_load.php`      | `VehicleController.php` (`show`) + `vehicle/show.blade.php`                                 |
| `ins_vehicule.php`       | `VehicleController.php` (`create`/`store`) + `vehicle/form.blade.php`                       |
| `upd_vehicule.php`       | `VehicleController.php` (`edit`/`update`)                                                   |
| `save_vehicule.php`      | `VehicleController.php` (`store`/`update`)                                                  |
| `del_vehicule.php`       | `VehicleController.php` (`destroy`)                                                         |
| `materiel_embarquer.php` | `VehicleController::equipmentAttach/Detach` — assign/unassign equipment from vehicle show page |
| `edit_categorie_consommable.php` | `ReferenceController::consumableCategory*` + `admin/references/consumable-category.blade.php` |
| `save_edit_categorie_consommable.php` | `ReferenceController::consumableCategoryStore/Update` |
| `del_categorie_consommable.php` | `ReferenceController::consumableCategoryDestroy` |
| `vehicule_xls.php`       | `VehicleController.php` (XLS/CSV list export)                                               |
| `vehicule_load.php` (event history tab) | `VehicleController::show()` — full year-filtered paginated event history with function type + km stats (was last-10-only) |
| `paramfnv.php`           | `ReferenceController.php` (`vehicleFunction*`) + `admin/references/vehicle-function.blade.php` |
| `paramfnv_edit.php`      | `ReferenceController.php`                                                                   |
| `paramfnv_save.php`      | `ReferenceController.php` (`vehicleFunctionStore`/`Update`)                                 |
| `type_vehicule.php`      | `ReferenceController.php` (`typeVehicule*`) + `admin/references/type-vehicule.blade.php`    |
| `save_type_vehicule.php` | `ReferenceController.php` (`typeVehiculeStore`)                                             |
| `upd_type_vehicule.php`  | `ReferenceController.php` (`typeVehiculeUpdate`)                                            |
| `del_type_vehicule.php`  | `ReferenceController.php` (`typeVehiculeDestroy`)                                           |

## Equipment (matériel)

| Legacy file                 | New implementation                                                                          |
| --------------------------- | ------------------------------------------------------------------------------------------- |
| `materiel.php`              | `app/Http/Controllers/EquipmentController.php` + `resources/views/equipment/index.blade.php` |
| `materiel_load.php`         | `EquipmentController.php`                                                                   |
| `ins_materiel.php`          | `EquipmentController.php`                                                                   |
| `upd_materiel.php`          | `EquipmentController.php`                                                                   |
| `upd_materiel_selector.php` | `EquipmentController.php`                                                                   |
| `save_materiel.php`         | `EquipmentController.php`                                                                   |
| `del_materiel.php`          | `EquipmentController.php`                                                                   |
| `materiel_embarquer.php`    | **WIP**                                                                                     |
| `materiel_xls.php`          | `EquipmentController.php` (XLS/CSV export)                                                  |
| `type_materiel.php`         | `ReferenceController.php` (`typeMateriel*` + `categorieMateriel*`) + `admin/references/`    |
| `ins_type_materiel.php`     | `ReferenceController.php` (`typeMaterielStore`)                                             |
| `save_type_materiel.php`    | `ReferenceController.php` (`typeMaterielStore`)                                             |
| `upd_type_materiel.php`     | `ReferenceController.php` (`typeMaterielUpdate`)                                            |
| `del_type_materiel.php`     | `ReferenceController.php` (`typeMaterielDestroy`)                                           |

## Consumables

| Legacy file                           | New implementation                                                                                |
| ------------------------------------- | ------------------------------------------------------------------------------------------------- |
| `consommable.php`                     | `app/Http/Controllers/ConsumableController.php` + `resources/views/consumable/index.blade.php`    |
| `consommable_load.php`                | `ConsumableController.php`                                                                        |
| `save_consommable.php`                | `ConsumableController.php`                                                                        |
| `upd_consommable.php`                 | `ConsumableController.php`                                                                        |
| `del_consommable.php`                 | `ConsumableController.php`                                                                        |
| `consommable_xls.php`                 | `ConsumableController.php` (XLS/CSV export)                                                       |
| `type_consommable.php`                | `ReferenceController.php` (`typeConsommable*`) + `admin/references/type-consommable.blade.php`    |
| `save_type_consommable.php`           | `ReferenceController.php` (`typeConsommableStore`)                                               |
| `upd_type_consommable.php`            | `ReferenceController.php` (`typeConsommableUpdate`)                                              |
| `del_type_consommable.php`            | `ReferenceController.php` (`typeConsommableDestroy`)                                             |
| `edit_categorie_consommable.php`      | **WIP**                                                                                           |
| `save_edit_categorie_consommable.php` | **WIP**                                                                                           |
| `del_edit_categorie_consommable.php`  | **WIP**                                                                                           |

## Dues / finance (cotisations)

| Legacy file                   | New implementation                                                                              |
| ----------------------------- | ----------------------------------------------------------------------------------------------- |
| `cotisations.php`             | `app/Http/Controllers/DuesController.php` + `resources/views/dues/index.blade.php`              |
| `cotisation_edit.php`         | `DuesController.php` (`batchSave`)                                                              |
| `save_cotisations.php`        | `DuesController.php` (`batchSave`)                                                              |
| `cotisations_xls.php`         | `DuesController.php` (`export`)                                                                 |
| `cotisations_extract.php`     | `DuesController.php` (`export`)                                                                 |
| `report_cotisations.php`      | **WIP**                                                                                         |
| `prelevements.php`            | `DuesController.php` (`directDebits`) + `dues/direct-debits.blade.php`                          |
| `save_prelevements.php`       | `DuesController.php` (`saveDirectDebits`)                                                       |
| `config_prelevements.php`     | **WIP**                                                                                         |
| `virements.php`               | `DuesController.php` (`transfers`) + `dues/transfers.blade.php`                                 |
| `virements_extract.php`       | `DuesController.php` (`transfers`) — bridge redirects to `dues.transfers`                       |
| `element_facturable.php`      | **WIP**                                                                                         |
| `save_element_facturable.php` | **WIP**                                                                                         |
| `del_element_facturable.php`  | **WIP**                                                                                         |
| `save_detail_facture.php`     | **WIP**                                                                                         |
| `note_frais_edit.php`         | **WIP**                                                                                         |
| `note_frais_save.php`         | **WIP**                                                                                         |
| `edit_categorie.php`          | **WIP**                                                                                         |
| `save_edit_categorie.php`     | **WIP**                                                                                         |
| `del_edit_categorie.php`      | **WIP**                                                                                         |
| `paramfn.php`                 | **WIP** (billable function params)                                                              |
| `paramfn_edit.php`            | **WIP**                                                                                         |
| `paramfn_save.php`            | **WIP**                                                                                         |

## Grades

| Legacy file                      | New implementation                                                               |
| -------------------------------- | -------------------------------------------------------------------------------- |
| `grades_load.php`                | `ReferenceController.php` (`gradeIndex`) + `admin/references/grade.blade.php`     |
| `edit_grades.php`                | `ReferenceController.php` (`gradeIndex`)                                         |
| `save_grades.php`                | `ReferenceController.php`                                                        |
| `upd_grades.php`                 | `ReferenceController.php`                                                        |
| `del_grade.php`                  | `ReferenceController.php`                                                        |
| `configuration_icone_grade.php`  | `ReferenceController.php` (`gradeIconUpload`/`gradeIconDestroy`)                 |
| `edit_categorie_grades.php`      | `ReferenceController.php` (`gradeCategory*`) + `admin/references/grade-category.blade.php` |
| `save_edit_categorie_grades.php` | `ReferenceController.php` (`gradeCategoryStore`/`Update`)                        |
| `del_categorie_grade.php`        | `ReferenceController.php` (`gradeCategoryDestroy` — blocked if grades assigned)  |

## Duty / on-call (gardes, astreintes, piquets)

| Legacy file                | New implementation                                                                                       |
| -------------------------- | -------------------------------------------------------------------------------------------------------- |
| `astreintes.php`           | `app/Http/Controllers/DutyController.php` (`astreintes`) + `resources/views/duty/astreintes.blade.php`   |
| `astreinte_edit.php`       | `DutyController.php`                                                                                     |
| `astreinte_save.php`       | `DutyController.php`                                                                                     |
| `astreintes_updates.php`   | `DutyController.php`                                                                                     |
| `feuille_garde.php`        | `DutyController.php` (`index`) + `duty/index.blade.php`                                                  |
| `save_garde.php`           | `DutyController.php`                                                                                     |
| `tableau_garde.php`        | `DutyController.php` (`index`) — bridge redirects to `duty.index`                                        |
| `tableau_garde_create.php` | **WIP**                                                                                                  |
| `tableau_garde_status.php` | **WIP**                                                                                                  |
| `tableau_garde_xls.php`    | `DutyController.php` (monthly on-call/astreinte roster XLS/CSV)                                          |
| `auto_garde.php`           | **WIP** (automatic guard generation)                                                                    |
| `automaticPiquet.php`      | **WIP**                                                                                                  |
| `save_piquet.php`          | **WIP**                                                                                                  |
| `type_garde.php`           | `DutyTypeController.php` (`index`) + `duty/types.blade.php`                                              |
| `save_type_garde.php`      | `DutyTypeController.php` (`store`/`update`)                                                              |
| `del_type_garde.php`       | `DutyTypeController.php` (`destroy` — blocked if guards exist)                                           |
| `demande_renfort.php`      | `EventController.php` (`renfortRequest` card + manage page); transmit-to-section still **WIP**           |

## Availability / unavailability / replacements

| Legacy file             | New implementation                                                                                 |
| ----------------------- | -------------------------------------------------------------------------------------------------- |
| `dispo.php`             | `app/Http/Controllers/AvailabilityController.php` + `resources/views/availability/index.blade.php` |
| `save_dispo.php`        | `AvailabilityController.php`                                                                       |
| `indispo.php`           | `app/Http/Controllers/UnavailabilityController.php` + `resources/views/unavailability/index.blade.php` |
| `indispo_choice.php`    | `UnavailabilityController.php`                                                                     |
| `indispo_display.php`   | `UnavailabilityController.php`                                                                     |
| `indispo_save.php`      | `UnavailabilityController.php`                                                                     |
| `indispo_status.php`    | `UnavailabilityController.php`                                                                     |
| `indispo_list_xls.php`  | **WIP**                                                                                            |
| `remplacements.php`     | `app/Http/Controllers/ReplacementController.php` + `resources/views/replacement/index.blade.php`   |
| `remplacement_edit.php` | `ReplacementController.php` (+ list XLS/CSV export)                                                |
| `intervention_edit.php` | **WIP**                                                                                            |
| `repos_saisie.php`      | **WIP**                                                                                            |
| `repos_save.php`        | **WIP**                                                                                            |

## Planning / calendar

| Legacy file        | New implementation                                                                         |
| ------------------ | ------------------------------------------------------------------------------------------ |
| `planning.php`     | `app/Http/Controllers/PlanningController.php` + `resources/views/planning/index.blade.php` |
| `planning_xls.php` | **WIP**                                                                                    |
| `calendar.php`     | `PlanningController.php`                                                                   |

## Documents

| Legacy file                                   | New implementation                                                                          |
| --------------------------------------------- | ------------------------------------------------------------------------------------------- |
| `documents.php`                               | `DocumentController@index` + `DocumentService` + `document/index.blade.php` (ob-* explorer) |
| `document_modal.php`                          | `document/index.blade.php` (upload/edit modals)                                             |
| `upd_document.php` / `save_documents.php`     | `DocumentController@store/update/destroy` (permission 47)                                   |
| `save_folder.php` / `upd_folder.php`          | `DocumentController@folderStore/folderUpdate/folderDestroy`                                 |
| `delete_file.php`                             | `DocumentController.php`                                                                    |
| `delete_event_file.php`                       | **WIP**                                                                                     |
| `upload.php`                                  | `DocumentController.php`                                                                    |
| `showfile.php`                                | `DocumentController@download` for library docs; still bridged for entity files              |
| `config_doc.php`                              | Not library config — PDF attestation/convention text (tracked under the PDF/billing items)  |
| Document type config                          | `DocumentTypeController.php` + `document/types.blade.php` (`type_document` CRUD, perm 47)   |
| `observations_modal.php`                      | **WIP**                                                                                     |
| `document_folders` / `document_security` docs | see `archive/legacy_app/documentation/` below                                               |

## Messaging / mail / SMS / alerts / chat

| Legacy file             | New implementation                                                                       |
| ----------------------- | ---------------------------------------------------------------------------------------- |
| `message.php`           | `app/Http/Controllers/MessageController.php` + `resources/views/message/index.blade.php` |
| `delete_message.php`    | `MessageController.php`                                                                  |
| `mail_create.php`       | **WIP**                                                                                  |
| `mail_create_input.php` | **WIP**                                                                                  |
| `mail_send.php`         | **WIP**                                                                                  |
| `mailer.php`            | **WIP**                                                                                  |
| `mailto.php`            | **WIP**                                                                                  |
| `destinataires.php`     | **WIP**                                                                                  |
| `chat.php`              | **WIP**                                                                                  |
| `chat_message.php`      | **WIP**                                                                                  |
| `alerte_create.php`     | **WIP**                                                                                  |
| `alerte_send.php`       | **WIP**                                                                                  |
| `reminder.php`          | **WIP**                                                                                  |
| `histo_sms.php`         | **WIP**                                                                                  |
| `push_monitor.php`      | **WIP**                                                                                  |
| `rss.php`               | **WIP**                                                                                  |

## Geolocation / maps

| Legacy file                   | New implementation                                                                                              |
| ----------------------------- | --------------------------------------------------------------------------------------------------------------- |
| `gps.php`                     | `app/Http/Controllers/GeolocationController.php` + `resources/views/personnel/geolocation.blade.php`            |
| `gps_save.php`                | `GeolocationController.php`                                                                                     |
| `gps_save2.php`               | `GeolocationController.php`                                                                                     |
| `gmaps_personnel.php`         | `GeolocationController.php`                                                                                     |
| `gmaps_evenement.php`         | **WIP**                                                                                                         |
| `geolocalize_all_persons.php` | `GeolocationController.php`                                                                                     |
| `localize.php`                | **WIP**                                                                                                         |
| `localize_me.php`             | **WIP**                                                                                                         |
| `localize_send.php`           | **WIP**                                                                                                         |
| `map.php`                     | **WIP**                                                                                                         |
| `jvectormap.php`              | `OrganizationController.php` (`cartographie`) + `resources/views/organization/cartographie.blade.php` (Leaflet) |
| `departement.php`             | `OrganizationController.php` (`sections` + CRUD) + `resources/views/organization/sections.blade.php`            |
| `zipcode.php`                 | **WIP**                                                                                                         |
| `buildzipcode.php`            | **WIP**                                                                                                         |

## Statistics / reporting / audit

| Legacy file               | New implementation                                                                                       |
| ------------------------- | -------------------------------------------------------------------------------------------------------- |
| `bilans.php`              | `app/Http/Controllers/StatisticsController.php` + `resources/views/statistics/index.blade.php`           |
| `bilan_participation.php` | `StatisticsController.php`                                                                              |
| `delete_statistique.php`  | `StatisticsController.php`                                                                              |
| `history.php`             | `app/Http/Controllers/AdminController.php` (`monitoring`) + `resources/views/admin/monitoring.blade.php` |
| `audit.php`               | `AdminController.php` (`monitoring`)                                                                     |

## Company / configuration / settings

| Legacy file               | New implementation                                                                                   |
| ------------------------- | ---------------------------------------------------------------------------------------------------- |
| `configuration.php`       | `app/Http/Controllers/AdminController.php` (`settings`) + `resources/views/admin/settings.blade.php` |
| `save_configuration.php`  | `AdminController.php` (`saveSetting`/`uploadSetting`/`deleteSetting`)                                |
| `configuration_db.php`    | `MaintenanceController.php`                                                                          |
| `configuration_theme.php` | `AdminController.php` (`uploadSetting`/`deleteSetting` — theme images)                               |
| `parametrage.php`         | `app/Http/Controllers/ReferenceController.php` (`index`) + `admin/references/index.blade.php`        |
| `company.php`             | `app/Http/Controllers/CompanyController.php` + `resources/views/company/index.blade.php`             |
| `save_company.php`        | `CompanyController.php`                                                                              |
| `upd_company.php`         | `CompanyController.php`                                                                              |
| `ins_company.php`         | `CompanyController.php`                                                                              |
| `del_company.php`         | `CompanyController.php`                                                                              |
| `upd_company_role.php`    | **WIP**                                                                                              |
| `company_xls.php`         | `CompanyController.php` (client list XLS/CSV export)                                                  |
| `menu_status_set.php`     | `app/Http/Controllers/ShortcutController.php`                                                        |

## Backup / restore / maintenance / upgrade

| Legacy file                | New implementation                                                                                     |
| -------------------------- | ------------------------------------------------------------------------------------------------------ |
| `backup.php`               | `app/Http/Controllers/BackupController.php` + `resources/views/admin/backup/index.blade.php`           |
| `restore.php`              | `BackupController.php` (`restore`)                                                                     |
| `fonctions_backup.php`     | `BackupController.php`                                                                                 |
| `database_maintenance.php` | `app/Http/Controllers/MaintenanceController.php` + `resources/views/admin/maintenance/index.blade.php` |
| `upgrade.php`              | `MaintenanceController.php` (migration status)                                                         |
| `update_app.php`           | **WIP**                                                                                                |
| `update_page.php`          | **WIP**                                                                                                |
| `buildsql.php`             | **WIP**                                                                                                |
| `decrypt.php`              | **WIP**                                                                                                |
| `import_api.php`           | **WIP**                                                                                                |
| `phpinfo.php`              | **WIP** (see `admin/maintenance` system info)                                                          |
| `browscap.php`             | **WIP**                                                                                                |
| `debug_data.php`           | **WIP**                                                                                                |

## Add-ons / packages

| Legacy file            | New implementation                                                              |
| ---------------------- | ------------------------------------------------------------------------------- |
| `addons.php`           | `admin.plugins` (redirect) — community plugins, WIP placeholder                 |
| `addons_save.php`      | `admin.fonctionnalites` (redirect) — feature/module toggles now in `ob_feature` |
| `install_addon.php`    | **WIP**                                                                         |
| `download_addon.php`   | **WIP**                                                                         |
| `download_module.php`  | **WIP**                                                                         |
| `download_package.php` | **WIP**                                                                         |

## PDF generation

| Legacy file                        | New implementation                                                                                                                 |
| ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `pdf.php`                          | **WIP**                                                                                                                            |
| `pdf_asa.php`                      | **WIP**                                                                                                                            |
| `pdf_attestation_fiscale.php`      | **WIP**                                                                                                                            |
| `pdf_attestation_formation.php`    | **WIP**                                                                                                                            |
| `pdf_bilans.php`                   | `StatistiqueController.php` (bilan annuel) — client-side pdf-lib (`resources/js/ob-pdf-bilan.js`) — **WIP** (parity not verified)  |
| `pdf_bulletin.php`                 | **WIP**                                                                                                                            |
| `pdf_carte_adherent.php`           | `PersonnelController.php` (`carteData`) + `PersonnelExportService.php` — client-side pdf-lib (`resources/js/ob-pdf-personnel.js`)  |
| `pdf_courrier_nouvel_adherent.php` | **WIP**                                                                                                                            |
| `pdf_diplome.php`                  | **WIP**                                                                                                                            |
| `pdf_document.php`                 | **WIP**                                                                                                                            |
| `pdf_livret.php`                   | `PersonnelController.php` (`livretData`) + `PersonnelExportService.php` — client-side pdf-lib (`resources/js/ob-pdf-personnel.js`) |
| `export_badges.php`                | **WIP**                                                                                                                            |

## Exports

| Legacy file             | New implementation                                                                              |
| ----------------------- | ----------------------------------------------------------------------------------------------- |
| `export.php`            | **WIP** (per-module XLS/CSV exports exist on individual controllers)                            |
| `export-html.php`       | **WIP**                                                                                         |
| `export-sql.php`        | **WIP**                                                                                         |
| `export-sql-liste.php`  | **WIP**                                                                                         |
| `export-tcd.php`        | **WIP**                                                                                         |
| `export-txt.php`        | **WIP**                                                                                         |
| `export-xls.php`        | **WIP**                                                                                         |
| `iCalcreator.class.php` | `app/Services/ICalExportService.php` (Sabre VObject), used by `EvenementController::exportIcal` |

## Emergency ops (DPS / SITAC / victims)

| Legacy file             | New implementation |
| ----------------------- | ------------------ |
| `dps.php`               | **WIP**            |
| `dps_calc.php`          | **WIP**            |
| `dps_save.php`          | **WIP**            |
| `sitac.php`             | **WIP**            |
| `sitac_options.php`     | **WIP**            |
| `sitac_save.php`        | **WIP**            |
| `victimes.php`          | **WIP**            |
| `liste_victimes.php`    | **WIP**            |
| `scan_victime.php`      | **WIP**            |
| `intervention_edit.php` | **WIP**            |

## QR codes / scanning / misc utilities

| Legacy file           | New implementation                   |
| --------------------- | ------------------------------------ |
| `qrcode.php`          | **WIP**                              |
| `qrcode_pic.php`      | **WIP**                              |
| `cav_edit.php`        | **WIP**                              |
| `paginator.class.php` | N/A — replaced by Laravel pagination |

## Shared procedural helper library (`fonctions_*`)

These are being dissolved into Eloquent models, controllers and form requests
rather than ported as standalone files.

| Legacy file                 | New implementation                                                    |
| --------------------------- | --------------------------------------------------------------------- |
| `fonctions.php`             | Distributed across `app/Models/*` and controllers                     |
| `fonctions_sql.php`         | N/A — replaced by Eloquent / query builder                            |
| `fonctions_parameters.php`  | `ParametrageController.php` / `AdminController.php`                   |
| `fonctions_menu.php`        | `resources/views/layout/sidebar.blade.php` + `ShortcutController.php` |
| `fonctions_infos.php`       | `DashboardController.php` (widgets)                                   |
| `fonctions_chart.php`       | `StatisticsController.php`                                            |
| `fonctions_gardes.php`      | `DutyController.php`                                                  |
| `fonctions_gardes_auto.php` | **WIP**                                                               |
| `fonctions_documents.php`   | `DocumentController.php`                                              |
| `fonctions_map.php`         | `GeolocationController.php`                                           |
| `fonctions_bank.php`        | `DuesController.php` (partial)                                        |
| `fonctions_import.php`      | **WIP**                                                               |
| `fonctions_unzip.php`       | **WIP**                                                               |
| `fonctions_mail.php`        | **WIP**                                                               |
| `fonctions_sms.php`         | **WIP**                                                               |
| `fonctions_dps.php`         | **WIP**                                                               |
| `fonctions_specific.php`    | **WIP**                                                               |

---

## `api/` (REST import/export)

All endpoints are **WIP** — no equivalent under `routes/api.php` yet.

| Legacy file                         | New implementation |
| ----------------------------------- | ------------------ |
| `api/index.php`                     | **WIP**            |
| `api/export/index.php`              | **WIP**            |
| `api/export/search.php`             | **WIP**            |
| `api/export/test/index.php`         | **WIP**            |
| `api/export/test/search_people.php` | **WIP**            |
| `api/import/index.php`              | **WIP**            |
| `api/import/event.php`              | **WIP**            |
| `api/import/people.php`             | **WIP**            |
| `api/import/test/index.php`         | **WIP**            |
| `api/import/test/event.php`         | **WIP**            |
| `api/import/test/insert_people.php` | **WIP**            |
| `api/import/test/update_people.php` | **WIP**            |

## `conf/`

| Legacy file                  | New implementation                           |
| ---------------------------- | -------------------------------------------- |
| `conf/index.php`             | N/A — replaced by Laravel `config/` + `.env` |
| `conf/optional.php.template` | N/A — replaced by `.env.example`             |

## `documentation/`

Design notes; not application code. Kept for reference.

| Legacy file                                            | New implementation                                  |
| ------------------------------------------------------ | --------------------------------------------------- |
| `documentation/db-info_document_folders.md`            | Reference (informs `DocumentController.php` schema) |
| `documentation/db-info_document_security.md`           | Reference                                           |
| `documentation/db_modify_document_security_options.md` | Reference                                           |

## `lib/` (third-party modules — names only)

Replaced by Composer packages where a Laravel equivalent exists; otherwise **WIP**.

| Legacy module       | New implementation                                                                                                                                             |
| ------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `lib/PBKDF2/`       | N/A — replaced by Laravel Hash/bcrypt                                                                                                                          |
| `lib/PHPMailer/`    | N/A — replaced by Laravel Mail (when mail ported)                                                                                                              |
| `lib/SMSGatewayMe/` | **WIP**                                                                                                                                                        |
| `lib/fpdf/`         | Replaced by client-side pdf-lib (`resources/js/ob-pdf-personnel.js`, `ob-pdf-bilan.js`) for livret / carte / bilans; remaining `pdf_*.php` pages still **WIP** |
| `lib/phpqrcode/`    | **WIP**                                                                                                                                                        |
| `lib/vendor/`       | N/A — replaced by Composer `vendor/`                                                                                                                           |
| `lib/index.php`     | N/A                                                                                                                                                            |

---

## Front-end assets

### `css/`

The legacy Bootstrap/jQuery stylesheets are replaced by the token-based CSS under
`resources/css/` (compiled via Vite). Vendor CSS (Bootstrap, datepicker, select,
table, toggle, croppie, jvectormap) is **WIP** / dropped in favour of the new
component system.

| Legacy file                                                                | New implementation                                                        |
| -------------------------------------------------------------------------- | ------------------------------------------------------------------------- |
| `css/main.css`                                                             | `resources/css/base.css`, `components.css`, `layout.css`, `variables.css` |
| `css/login.css`                                                            | `resources/css/login.css`                                                 |
| `css/print.css`, `css/export-print.css`                                    | **WIP**                                                                   |
| `css/Chart.css`                                                            | **WIP**                                                                   |
| `css/imginput.css`                                                         | `resources/css/ob-avatar.css`                                             |
| `css/all.css`, `css/css.php`, `css/index.php`                              | N/A — replaced by Vite build                                              |
| `css/bootstrap*.css`, `css/croppie.css`, `css/jquery-jvectormap-2.0.5.css` | **WIP** (vendor)                                                          |

### `js/`

Legacy jQuery page scripts are replaced by per-page ES modules under
`resources/js/` (Vite). Vendor libraries (jQuery, Bootstrap bundle, Chart, moment,
fullcalendar, tablesorter, tinymce, tokeninput, jvectormap, croppie, ddslick,
swal, etc.) are **WIP** / being dropped.

| Legacy file(s)                                                                                                                                                                                                                                                                                                               | New implementation                                          |
| ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| `js/personnel.js`, `js/personnel_liste.js`                                                                                                                                                                                                                                                                                   | `resources/js/ob-personnel-index.js`                        |
| `js/save_personnel.js`                                                                                                                                                                                                                                                                                                       | `resources/js/ob-personnel-form.js`                         |
| `js/evenement*.js`                                                                                                                                                                                                                                                                                                           | `resources/js/ob-evenement-form.js`, `ob-evenement-show.js` |
| `js/cotisations.js`                                                                                                                                                                                                                                                                                                          | `resources/js/ob-cotisations-index.js`                      |
| `js/vehicule.js`, `js/upd_vehicule.js`                                                                                                                                                                                                                                                                                       | `resources/js/ob-vehicule-form.js`                          |
| `js/gps.js`                                                                                                                                                                                                                                                                                                                  | `resources/js/ob-geolocalisation.js`                        |
| `js/login-general.js`                                                                                                                                                                                                                                                                                                        | `resources/js/ob-auth-login.js`                             |
| `js/theme.js`                                                                                                                                                                                                                                                                                                                | `resources/js/ob-sidebar.js`, `ob-shortcuts.js`             |
| `js/all.js`, `js/checkForm.js`, `js/dateFunctions.js`, etc.                                                                                                                                                                                                                                                                  | `resources/js/app.js` (shared helpers)                      |
| Other page scripts (`consommable.js`, `materiel.js`, `dispo.js`, `indispo.js`, `planning.js`, `section.js`, `equipe.js`, `poste.js`, `qualifications.js`, `documents.js`, `chat.js`, `habilitations.js`, `tableau_garde.js`, `feuille_garde.js`, `remplacement*.js`, `note_de_frais.js`, `sitac`/`victimes`/`scanner`, etc.) | **WIP**                                                     |
| Vendor: `jquery*.js`, `bootstrap*.js`, `Chart.bundle.min.js`, `moment-with-locales.min.js`, `js/fullcalendar/`, `js/tablesorter/`, `js/tinymce/`, `js/tokeninput/`, `js/columnFilters/`, `js/scanner/`                                                                                                                       | **WIP** (vendor; mostly dropped)                            |
| `js/color.php`, `js/index.php`                                                                                                                                                                                                                                                                                               | N/A — replaced by Vite build                                |

### `images/`

Static binary assets. UI chrome icons are superseded by the new component CSS;
domain/upload images are copied to `public/images/`.

| Legacy folder/file                         | New implementation                                                              |
| ------------------------------------------ | ------------------------------------------------------------------------------- |
| `images/` (root UI icons, gifs, logos)     | `public/images/` / replaced by `resources/css/` component styling               |
| `images/grades_sp/`, `images/grades_army/` | `public/images/` (grade icons, managed via `ParametrageController` grade icons) |
| `images/vehicules/`                        | `public/images/`                                                                |
| `images/evenements/`                       | `public/images/`                                                                |
| `images/gardes/`                           | `public/images/`                                                                |
| `images/flags/`                            | `public/images/`                                                                |
| `images/sitac/`                            | **WIP** (SITAC not ported)                                                      |
| `images/user-specific/`                    | `public/images/` (uploaded avatars / `storage`)                                 |

---

## Excluded from this map (per request)

`webfonts/`, `user-data/`, `sql/`, `scripts/` — not enumerated.
