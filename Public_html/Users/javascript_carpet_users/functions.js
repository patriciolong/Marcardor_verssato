const ModalManager = {
    open: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = "flex";
            
            console.log(`Modal ${modalId} abierto`);
        } 
    },

    close: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
           
            modal.style.display = 'none';
           
            document.body.style.overflow = 'auto'; 
        } 
    }
}

