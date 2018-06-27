(function () {
    //Section 1 : 按下自定义按钮时执行的代码
    var a = {
        exec: function (editor) {
			frame_obj.photo_choice_init('', '', editor.name, 'editor');
        }
    },
    b = 'ueeshopimage';
    CKEDITOR.plugins.add(b, {
        init: function (editor) {
            editor.addCommand(b, a);
            editor.ui.addButton('ueeshopimage', {
                label: editor.lang.ueeshopimage.title,
                icon: this.path + 'icon.png',
                command: b
            });
        }
    });
})(); 