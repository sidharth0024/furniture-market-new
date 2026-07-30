        </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="./assets/js/bootstrap.bundle.min.js"></script>
        <script src="./assets/js/main.js"></script>
        <script src="https://cdn.datatables.net/v/dt/dt-3.0.0/datatables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

        <script>

            $(function() {
                
                $('#editor1,#editor2,#editor3,#editor4,#editor5,#editor6').summernote({
                    
                    height: 300,

                    placeholder: 'Write here...',

                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                    
                });
                
            });
            const table = new DataTable('#example', {
                // Pagination
                paging: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],

                // Search
                searching: true,

                // Sorting
                ordering: true,
                // order: [
                //     [0, 'asc']
                // ], // Sort by first column

                // Information
                info: true,

                // Responsive
                responsive: true,

                // Horizontal Scroll
                scrollX: true,

                // Auto Width
                autoWidth: false,

                // Save table state
                stateSave: true,

                // Processing Indicator
                processing: true,

                // Language
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No records available",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },

                // Column Definitions
                columnDefs: [{
                        targets: [0], // First column
                        orderable: false // Disable sorting
                    },
                    {
                        targets: [4],
                        searchable: false // Disable searching
                    }
                ],

                // Layout
                layout: {
                    topStart: 'pageLength',
                    topEnd: 'search',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                }
            });
        </script>
        </body>

        </html>