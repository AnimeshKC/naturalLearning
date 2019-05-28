function xmldb_block_natural_learning_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    /// Add a new column newcol to the mdl_natural_learning_options
    if ($oldversion < 2019011501) {
         if ($oldversion < 2019011501) {

        // Define field flag to be added to block_natural_learning.
        $table = new xmldb_table('block_natural_learning');
        $field = new xmldb_field('flag', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'title');

        // Conditionally launch add field flag.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Natural_learning savepoint reached.
        upgrade_block_savepoint(true, 2019011501, 'natural_learning');
    }

    }

    return true;
}