<script>
        // Initialize Icons
        lucide.createIcons();

        // Simple Tab Switching Logic
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active", "text-purple-700", "border-purple-600");
                tablinks[i].classList.add("text-slate-400", "border-transparent");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active", "text-purple-700", "border-purple-600");
            evt.currentTarget.classList.remove("text-slate-400", "border-transparent");
        }

        // Toggle Buttons (Visual only for PHP demo)
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const parent = this.closest('.toggle-group');
                parent.querySelectorAll('.toggle-btn').forEach(b => {
                    b.classList.remove('bg-purple-600', 'text-white', 'shadow-sm');
                    b.classList.add('text-slate-400');
                });
                this.classList.remove('text-slate-400');
                this.classList.add('bg-purple-600', 'text-white', 'shadow-sm');
                
                // Update hidden input if needed
                const input = parent.querySelector('input[type="hidden"]');
                if(input) input.value = this.dataset.value;
            });
        });
    </script>
</body>
</html>