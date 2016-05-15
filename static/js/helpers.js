var RGJ = {};

RGJ.helpers = {
    encode_file: function( file_object, callback ) {
        var reader = new FileReader();
        reader.onload = function() {
            var file_info = {
                data: btoa( reader.result ),
                type: file_object.type
            };

            callback( file_info );
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
