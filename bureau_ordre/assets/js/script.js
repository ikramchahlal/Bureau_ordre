// Initialisation des tooltips
$(document).ready(function(){
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Animation au chargement
    $('.animate-fade').css('opacity', 0).animate({opacity: 1}, 600);
    
    // Initialisation DataTables avec options
    $('.table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
        },
        responsive: true,
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        pageLength: 10
    });
    
    // Effet de hover sur les cartes
    $('.card').hover(
        function() {
            $(this).css('transform', 'translateY(-5px)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
        }
    );
    
    // Confirmation avant suppression
    $('.confirm-delete').click(function(){
        return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');
    });
});