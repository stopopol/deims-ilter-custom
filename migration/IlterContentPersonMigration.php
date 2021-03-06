<?php

/**
 * @file
 * Definition of IlterContentPersonMigration.
 */

class IlterContentPersonMigration extends DeimsContentPersonMigration {

//  protected $dependencies = array('DeimsFile','IlterTaxonomyILTERDiscipline','IlterTaxonomyNetwork','IlterTaxonomyEnvThesOLD');

  public function __construct(array $arguments) {
    parent::__construct($arguments);

    $this->removeFieldMapping(NULL,'field_person_state'); 
    $this->removeFieldMapping(NULL,'field_person_zipcode');
    $this->removeFieldMapping(NULL,'field_person_list');
    $this->removeFieldMapping('field_person_title');
    $this->removeFieldMapping(NULL,'field_person_fullname');

    // Person Link
    $this->addFieldMapping('field_url','field_person_online');
    $this->addFieldMapping('field_url:title','field_person_online:title');
    $this->addFieldMapping('field_url:attributes','field_person_online:attributes');
 
    // Person Photo
    $this->addFieldMapping('field_images','field_person_photo')
     ->sourceMigration('DeimsFile');
    $this->addFieldMapping('field_images:file_class')->defaultValue('MigrateFileFid');
    $this->addFieldMapping('field_images:preserve_files')->defaultValue(TRUE);

    // Vocabularies (other nets)
    $this->addFieldMapping('field_networks_term_ref','field_research_site_network')
     ->sourceMigration('IlterTaxonomyNetwork');
    $this->addFieldMapping('field_networks_term_ref:source_type')
      ->defaultValue('tid');

    // Vocabularies (3 term refs)
    $this->addFieldMapping('field_person_specialty_term','field_person_specilaty')
      ->sourceMigration('IlterTaxonomyILTERDiscipline');
    $this->addFieldMapping('field_person_specialty_term:source_type')
      ->defaultValue('tid');


    // field_person_subjects	Subjects Investigated (term ref subjects envTheOLD )
    $this->addFieldMapping('field_subjects_term_ref','field_person_subjects')
     ->sourceMigration('IlterTaxonomyEnvThesOLD');
    $this->addFieldMapping('field_subjects_term_ref:source_type')
      ->defaultValue('tid');

    $this->addFieldMapping('field_person_orcid','field_person_orcid');

    // Node ref to Ilter Natl. Network.
    $this->addFieldMapping('field_person_network_ref','field_person_network')
      ->sourceMigration('IlterContentILTERNationalNetwork');
/**
     First take: instead of creating Stubs, lets just ignore it for now.
    // Node Red to "site".
    $this->addFieldMapping('field_related_sites','field_dataset_site_name')
      ->sourceMigration('IlterContentSite');
*/
    $this->addFieldMapping('field_address:postal_code','field_person_postalcode');

    // field_person_pubs does not exist
    $this->removeFieldMapping(NULL, 'field_person_pubs');

    $this->addUnmigratedSources(array(
      'revision',
      'log',
      'upload',
      'upload:description',
      'upload:list',
      'upload:weight',
      'field_person_photo:list',
      'field_person_photo:description',
      '2',      //	ILTER Discipline  (this is a repeat of Specialty term ref!)
      'revision_uid',
      'field_person_pubs',
      'field_person_photo_data',
      'field_person_rel_dataset', // from the inverse relation - Related datasets
      'field_dataset_site_name',  // DNM
    ));

    $this->addUnmigratedDestinations(array(
      'field_person_title',
      'field_associated_biblio_author',
      'field_images:language',
      'field_images:alt',
      'field_images:title',
      'field_person_specialty_term:create_term',
      'field_person_specialty_term:ignore_case',
      'field_networks_term_ref:create_term',
      'field_networks_term_ref:ignore_case',
      'field_address:administrative_area',
      'field_subjects_term_ref:create_term',
      'field_subjects_term_ref:ignore_case',
      'field_person_orcid:language',
    ));

//        'field_list_in_directory',  <<== default to '1'  ?
    $this->addFieldMapping('field_list_in_directory')
      ->defaultValue('1');
  }


  public function prepareRow($row) {

    parent::prepareRow($row);

    // Fix empty email values used on SEV.
    switch ($row->field_person_email) {
      case 'none@none.com':
        $row->field_person_email = NULL;
    }

  }

  public function getOrganization($node, $row) {
    $field_values = array();

    // Search for an already migrated organization entity with the same title
    // and link value.
    if (!empty($row->{'field_person_organization:title'})) {
      $query = new EntityFieldQuery();
      $query->entityCondition('entity_type', 'node');
      $query->entityCondition('bundle', 'organization');
      $query->propertyCondition('title', $row->{'field_person_organization:title'});
      $results = $query->execute();
      if (!empty($results['node'])) {
        $field_values[] = array('target_id' => reset($results['node'])->nid);
      }
    }

    return $field_values;
  }

}
