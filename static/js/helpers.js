var RGJ = {};

RGJ.helpers = {
    encode_file: function( file_object, callback ) {
        var reader = new FileReader();
        reader.onload = function() {
            callback( btoa( reader.result ) );
        };

        reader.readAsBinaryString( file_object );
    },

    render_image: function( file_object, callback ) {
        var reader = new FileReader();
        reader.onload = function() {
            callback(reader.result);
        };

        reader.readAsDataURL( file_object );
    }
}
