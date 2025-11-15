<?php
include "../../db.php";

function mission($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='mission'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

if (isset($_POST['save_mission'])) {
    $content = $_POST['mission_content'];
    $stmt = $conn->prepare("UPDATE DIT_post SET content=? WHERE title='mission'");
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit(); // Important for AJAX
}


function vision($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='vision'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

if (isset($_POST['save_vision'])) {
    $content = $_POST['vision_content'];
    $stmt = $conn->prepare("UPDATE DIT_post SET content=? WHERE title='vision'");
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit(); // Important for AJAX
}


function quality_policy($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='quality policy'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

if (isset($_POST['save_quality_policy'])) {
    $content = $_POST['quality_policy_content'];
    $stmt = $conn->prepare("UPDATE DIT_post SET content=? WHERE title='quality policy'");
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit(); // Important for AJAX
}


function college_goals($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='college goals'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

if (isset($_POST['save_college_goals'])) {
    $content = $_POST['college_goals_content'];
    $stmt = $conn->prepare("UPDATE DIT_post SET content=? WHERE title='college goals'");
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit(); // Important for AJAX
}

function core_values($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='core values'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

if (isset($_POST['save_core_values'])) {
    $content = $_POST['core_values_content'];
    $stmt = $conn->prepare("UPDATE DIT_post SET content=? WHERE title='core values'");
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit(); // Important for AJAX
}


function about_department($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='about the department'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

if (isset($_POST['save_about_department'])) {
    $content = $_POST['about_department_content'];
    $stmt = $conn->prepare("UPDATE DIT_post SET content=? WHERE title='about the department'");
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit(); // Important for AJAX
}


function program_offerings($db)
{
    $query = "SELECT * FROM DIT_post WHERE title='program offerings'";
    $result = $db->query($query);
    return $result->fetch_assoc();
}

if (isset($_POST['save_program_offerings'])) {
    $content = $_POST['program_offerings_content'];
    $stmt = $conn->prepare("UPDATE DIT_post SET content=? WHERE title='program offerings'");
    $stmt->bind_param("s", $content);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit(); // Important for AJAX
}
