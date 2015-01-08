@javascript
Feature: Import groups
  In order to reuse the groups of my products
  As a product manager
  I need to be able to import groups

  Scenario: Successfully import standard groups
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
      | RELATED |
    And the following product groups:
      | code           | label       | type    | axis        |
      | ORO_TSHIRT     | Oro T-shirt | VARIANT | size, color |
      | AKENEO_VARIANT | Akeneo      | VARIANT | size        |
    And the following CSV file to import:
    """
    code;label-en_US;label-fr_FR;type;attributes
    default;;;RELATED;
    AKENEO_MUG;Akeneo Mug;Tasse Akeneo;VARIANT;color
    AKENEO_TSHIRT;Akeneo T-Shirt;T-Shirt Akeneo;VARIANT;color,size
    ORO_TSHIRT;Polo;Short;VARIANT;color,size
    AKENEO_VARIANT;;;VARIANT;size
    """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    And I should not see "This property cannot be changed"
    Then there should be the following groups:
      | code           | label-en_US    | label-fr_FR    | type    | attributes |
      | default        |                |                | RELATED |            |
      | AKENEO_MUG     | Akeneo Mug     | Tasse Akeneo   | VARIANT | color      |
      | AKENEO_TSHIRT  | Akeneo T-Shirt | T-Shirt Akeneo | VARIANT | color,size |
      | ORO_TSHIRT     | Polo           | Short          | VARIANT | color,size |
      | AKENEO_VARIANT |                |                | VARIANT | size       |

  Scenario: Fail to change group type with import
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following product groups:
      | code           | label       | type    | axis |
      | ORO_TSHIRT     | Oro T-shirt | VARIANT | size |
      | AKENEO_VARIANT | Akeneo      | VARIANT | size |
    And the following CSV file to import:
    """
    code;label-en_US;label-fr_FR;type;attributes
    AKENEO_VARIANT;;;RELATED;size
    """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    And I should see "This property cannot be changed"
    Then there should be the following groups:
      | code           | label-en_US    | label-fr_FR    | type    | attributes |
      | ORO_TSHIRT     | Oro T-shirt    |                | VARIANT | size       |
      | AKENEO_VARIANT | Akeneo         |                | VARIANT | size       |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip groups with empty code
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following product groups:
      | code           | label       | type    | axis        |
      | ORO_TSHIRT     | Oro T-shirt | VARIANT | size, color |
    And the following CSV file to import:
    """
    code;label-en_US;label-fr_FR;type;attributes
    ;Akeneo T-Shirt;T-Shirt Akeneo;VARIANT;color,size
    """
    And the following job "footwear_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_group_import" job to finish
    Then I should see "skipped 1"
    And I should see "code: This value should not be blank"
