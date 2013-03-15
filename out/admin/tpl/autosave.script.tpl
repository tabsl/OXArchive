[{if $oViewConf->isAutoSave() && $oxid != "-1"}]
    //writing autosave variable values
    var oMyedit = parent.edit.document.getElementById("myedit");
    if ( oMyedit != null ) {
        var iCtr = 0;
        var blAutosave = false;
        while ( oMyedit.elements.item( iCtr ) != null && oMyedit.elements.item( iCtr ).name != null ) {
            var sInputName = oMyedit.elements.item( iCtr ).name;
            if ( sInputName.search( 'autosave' ) != -1 ) {
                blAutosave = true;
                var oField  = oMyedit.elements.item( iCtr );
                var sName   = oField.getAttribute( 'elName' );

                var oSource = oMyedit.elements.namedItem( sName );

                //setting values
                if ( oSource != null ) {
                    if ( sName != 'cl' ) {
                        oField.value = oSource.value;
                    } else {
                        oField.value = sLocation;
                    }
                }
            }
            iCtr++;
        }

        // setting function to 'save'
        if ( blAutosave ) {
            // for saving HMLT editor contents
            if ( parent.edit.submit_form && parent.edit.CopyLongDesc ) {
                parent.edit.CopyLongDesc();
            }
            oMyedit.fnc.value = 'save';
            oMyedit.submit();
            return;
        }
    }
[{/if}]