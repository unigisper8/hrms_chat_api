<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GeoCode</title>
</head>
<body>
<!-- jQuery 2.1.1 -->
<script src="<?php echo base_url("assets/js/jquery-2.1.1.js"); ?>"></script>
<!-- Bootstrap -->
<script src="<?php echo base_url("assets/js/bootstrap.min.js"); ?>"></script>

<script>
    //global variables
    var baseurl = "https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=";
    $("document").ready(function () {
        $.ajax({
            url: baseurl + "الفنطاس",
            //type: "json",
            success: function (result) {
                console.log(result)
            }
        });
    });
</script>
</body>
</html>