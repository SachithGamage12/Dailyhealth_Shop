<?php
// Include your database connection
// Database connection
$conn = new mysqli("localhost", "u627928174_root", "Daily@365", "u627928174_daily_routine");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Fetch events (prioritize upcoming, then latest completed)
$query = "SELECT e.id, e.title, e.description, e.created_at, e.role, e.date, e.time, e.venue, ei.image_path 
          FROM events e 
          LEFT JOIN event_images ei ON e.id = ei.event_id
          ORDER BY CASE WHEN e.role = 'Upcoming' THEN 1 ELSE 2 END, e.created_at DESC LIMIT 3";

$result = $conn->query($query);

if ($result) {
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[$row['id']]['title'] = $row['title'];
        $events[$row['id']]['description'] = $row['description'];
        $events[$row['id']]['created_at'] = $row['created_at'];
        $events[$row['id']]['role'] = $row['role'];
        $events[$row['id']]['date'] = $row['date'];
        $events[$row['id']]['time'] = $row['time'];
        $events[$row['id']]['venue'] = $row['venue'];

        // Handle event images
        $events[$row['id']]['images'][] = $row['image_path'];
    }

    if (!empty($events)) {
        // The first event (Latest Upcoming) is the large event initially
        $firstEvent = array_shift($events);
?>
        <!-- Big Upcoming Event (Initially displayed large) -->
        <div class="col-12 col-md-6 col-lg-12 d-flex justify-content-center mb-4">
            <div class="event-card p-3 text-center w-100">
                <!-- Event Title First -->
                <h4 id="bigEventTitle" class="event-title mt-3"><?php echo htmlspecialchars($firstEvent['title']); ?></h4>
                
                <!-- Event Image Second -->
                <?php if (!empty($firstEvent['images'])): ?>
                    <div class="event-thumbnail" id="bigEventThumbnail" style="width: 100%; height: auto; overflow: hidden;">
                        <img id="bigEventImage" src="<?php echo './Admin/uploads/' . htmlspecialchars($firstEvent['images'][0]); ?>" 
                            alt="Event Image" 
                            class="img-fluid w-100 h-auto" 
                            style="object-fit: cover; border-radius: 10px;">
                    </div>
                <?php endif; ?>

                <!-- Event Description, Role, and Date Third -->
                <p id="bigEventDescription" class="event-description"><?php echo htmlspecialchars($firstEvent['description']); ?></p>
                <p id="bigEventRole" class="event-role text-muted"><?php echo htmlspecialchars($firstEvent['role']); ?></p>
                <p id="bigEventDate" class="text-muted event-date"><?php echo htmlspecialchars($firstEvent['created_at']); ?></p>
            </div>
        </div>

        <!-- Small Square Event Thumbnails (Including the upcoming event) -->
        <div class="col-12 d-flex flex-wrap justify-content-center" style="margin-top: -10px;">
            <?php 
            // Merge the first event back into the array to show it in the small boxes
            array_unshift($events, $firstEvent);
            $totalEvents = count($events);

            foreach ($events as $index => $event) { 
                $isLastItem = ($index === $totalEvents - 1);
            ?>
                <div class="col-4 col-md-2 p-2 d-flex justify-content-center">
                    <div class="event-thumbnail position-relative" 
                        style="width: 100px; height: 100px; overflow: hidden; border-radius: 10px; cursor: pointer;" 
                        onclick="<?php echo $isLastItem ? "window.location.href='login.php'" : "changeBigImage('./Admin/uploads/" . htmlspecialchars($event['images'][0]) . "', '" . htmlspecialchars($event['title']) . "', '" . htmlspecialchars($event['description']) . "', '" . htmlspecialchars($event['role']) . "', '" . htmlspecialchars($event['created_at']) . "')"; ?>">
                        
                        <?php if ($isLastItem) { ?>
                            <!-- See More Overlay for Last Card -->
                            <div class="see-more-overlay d-flex justify-content-center align-items-center" 
                                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); border-radius: 10px; z-index: 2;">
                                <span class="text-white font-weight-bold">See More</span>
                            </div>
                        <?php } ?>
                        
                        <!-- Small Event Image -->
                        <?php if (!empty($event['images'])): ?>
                            <img src="<?php echo './Admin/uploads/' . htmlspecialchars($event['images'][0]); ?>" 
                                alt="Event Image" 
                                class="img-fluid w-100 h-100" 
                                style="object-fit: cover; border-radius: 10px;">
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
        </div>
<?php
    }
} else {
    echo "Error: " . $conn->error;
}
?>
