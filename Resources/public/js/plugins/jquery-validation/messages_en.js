(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "../jquery.validate"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
}(function ($) {

    /*
     * Translated default messages for the jQuery validation plugin.
     * Locale: AR (Arabic; العربية)
     */
    $.extend($.validator.messages, {
        required: 'Please enter a value',
        remote: "يرجى تصحيح هذا الحقل للمتابعة",
        email: 'Invalid email address',
        url: "رجاء إدخال عنوان موقع إلكتروني صحيح",
        date: "رجاء إدخال تاريخ صحيح",
        dateISO: "رجاء إدخال تاريخ صحيح (ISO)",
        number: "رجاء إدخال عدد بطريقة صحيحة",
        digits: "عذراً , لا يمكن قبول الحروف والأرقام الكسريه",
        creditcard: "رجاء إدخال رقم بطاقة ائتمان صحيح",
        equalTo: "رجاء إدخال نفس القيمة",
        extension: "رجاء إدخال ملف بامتداد موافق عليه",
        maxlength: $.validator.format("The filed accept less than {0} characters "),
        minlength: $.validator.format('The field accept {0} characters or longer'),
        ckmin: $.validator.format('يجب أن تكون عدد الحروف {0} أو أكثر'),
        RequiredBirthdate: 'Please enter a value',
        rangelength: $.validator.format("عدد الحروف يجب أن يكون بين {0} و {1}"),
        range: $.validator.format("رجاء إدخال عدد قيمته بين {0} و {1}"),
        min: $.validator.format("عذراً, اقل قيمة ممكنه هي  {0}"),
        max: $.validator.format("عذرا,ً اكبر قيمة ممكنه  هي {0}"),
        mincheck: $.validator.format('رجاء إختيار عدد أكبر من أو يساوي {0}'),
        password: 'يجب ان تكون كلمة المرور مكونه من 8 خانات وتحتوي على ارقام وحروف',
        passwordMax: 'يجب ان تكون كلمة المرور مكونه من 4096 خانه على الأكثر وتحتوي على ارقام وحروف',
        unique: "غير متاح",
        filesize: $.validator.format('يجب الا يزيد حجم الصوره عن {0} ميجا'),
        dimensions: 'يجب الا تقل ابعاد الصورة عن 200*200',
        youtube: 'You should enter a youtube link',
        uploadrequired: 'You should enter at least one photo',
    });

}));