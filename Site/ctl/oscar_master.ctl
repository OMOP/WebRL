--------------------------------------------------------
-- Template for All Method Control Files
-- OSCAR_RESULTS load
-- Name			Date	  Description
-- -------------------- --------  ----------------------  
-- Christian Reich		2-Nov-2010	  Initial Release
--------------------------------------------------------
OPTIONS(COLUMNARRAYROWS=60000)
LOAD DATA
INTO TABLE OMOP_RESULTS.OSCAR_RESULTS
APPEND
FIELDS TERMINATED BY '|'
TRAILING NULLCOLS 
(
SOURCE_ID              CONSTANT '<DATASET>',
source_table_name, 
statistic_type nullif (statistic_type='.'), 
statistic_value nullif (statistic_value='.'), 
summary_level nullif (summary_level='.'), 
variable_description_level_1 nullif (variable_description_level_1='.'), 
variable_description_level_2 nullif (variable_description_level_2='.'), 
variable_description_level_3 nullif (variable_description_level_3='.'), 
variable_name nullif (variable_name ='.'), 
variable_type nullif (variable_type ='.'), 
variable_value nullif (variable_value ='.'), 
variable_value_level_1 nullif (variable_value_level_1='.'), 
variable_value_level_2 nullif (variable_value_level_2='.'), 
variable_value_level_3 nullif (variable_value_level_3='.')
)
