$(document).ready(() => {
    /**
     * Remove
     */
    $('.btn-remove').on('click', event => {
        const link = $(event.delegateTarget).attr('href');
        const trElement = $(event.delegateTarget).closest('tr');

        event.preventDefault();

        $(trElement).addClass('table-danger');

        if (confirm('Are you sure you want to delete this ?') === true) {
            fetch(link, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok !== true) {
                    console.log(response);

                    throw Error(response.statusText);
                }

                return response.json();
            }).then(response => {
                console.log(response);

                $(trElement).remove();
            }).catch(error => {
                console.error('Error:', error);
            });
        } else {
            $(trElement).removeClass('table-danger');
        }
    });

    /**
     * Update
     */
    $('input, select').on('change', event => {
        const name = $(event.target).attr('name');
        const labelElement = $(event.target).closest('.form-group').find('label');

        let data = {};
        data[name] = $(event.target).val();

        console.log(data);

        $(labelElement).removeClass('text-success text-danger')
            .find('.fas, .far').remove();

        fetch(window.app.api, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(response => {
            if (response.ok !== true) {
                console.log(response);

                throw Error(response.statusText);
            }

            return response.json();
        }).then(response => {
            console.log(response);

            $(labelElement).addClass('text-success')
                .append(' <i class="far fa-check-circle"></i>');
        }).catch(error => {
            console.error('Error:', error);

            $(labelElement).addClass('text-danger')
                .append(' <i class="fas fa-times-circle"></i>');
        });
    });
});
