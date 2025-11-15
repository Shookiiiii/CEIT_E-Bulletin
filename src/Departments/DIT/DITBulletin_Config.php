<?php
include "../../db.php";

function mission($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='mission'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}



function vision($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='vision'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function core_values($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='core values'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function quality_policy($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='quality policy'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}


function college_goals($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='college goals'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function about_department($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='about the department'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

function program_offerings($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='program offerings'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}
