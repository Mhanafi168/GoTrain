document.addEventListener('DOMContentLoaded', function() {
    var ticketId = "<?php echo htmlspecialchars($ticket['ticket_id'] ?? ''); ?>";
    var ticketData = JSON.stringify({
        id: ticketId,
        source: "<?php echo isset($ticket) ? addslashes($ticket['source_station']) : ''; ?>",
        destination: "<?php echo isset($ticket) ? addslashes($ticket['destination_station']) : ''; ?>",
        date: "<?php echo isset($ticket) ? addslashes($ticket['journey_date']) : ''; ?>"
    });
    var qr = qrcode(0, 'M');
    qr.addData(ticketData);
    qr.make();
    document.getElementById('qrcode').innerHTML = qr.createImgTag(3);
});

function sendTicketByEmail() {
    alert("Email functionality would be implemented here.");
    // Example AJAX call:
    // fetch('/send_ticket_email.php?id=<?php echo htmlspecialchars($ticket['ticket_id'] ?? ''); ?>')
    //     .then(response => response.json())
    //     .then(data => alert(data.message));
}