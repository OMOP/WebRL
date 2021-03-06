OPTIONS(SKIP=1)
LOAD DATA
INTO TABLE OMOP_RESULTS.SCCS_SUPPLEMENTAL_RESULTS_2
APPEND
FIELDS TERMINATED BY ','
TRAILING NULLCOLS
(
   ANALYSIS_ID            CONSTANT '<ANALYSIS>'
 , EXPERIMENT_ID          CONSTANT '<EXPERIMENT>'
 , SOURCE_ID              CONSTANT '<DATASET>'
 , DRUG_CONCEPT_ID        INTEGER EXTERNAL NULLIF (DRUG_CONCEPT_ID=BLANKS)
 , CONDITION_CONCEPT_ID   INTEGER EXTERNAL NULLIF (CONDITION_CONCEPT_ID=BLANKS)
 , SCORE                  "TO_NUMBER(decode(Trim(:SCORE), 'NA', NULL, '.', NULL, :SCORE))"
 , STANDARD_ERROR         "TO_NUMBER(decode(:STANDARD_ERROR, '.', NULL, '', NULL, :STANDARD_ERROR))"
 , BS_MEAN                "TO_NUMBER(decode(:BS_MEAN, '.', NULL, '', NULL, 'I', NULL, :BS_MEAN))"
 , BS_LOWER               "decode(:BS_LOWER, '.', NULL, '', NULL, :BS_LOWER)"
 , BS_UPPER               "decode(:BS_UPPER, '.', NULL, '', NULL, :BS_UPPER)"
 , BS_PROB0               "decode(:BS_PROB0, '.', NULL, '', NULL, :BS_PROB0)"
)

