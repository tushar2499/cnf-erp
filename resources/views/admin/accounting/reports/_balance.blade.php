{{ number_format(abs($amount), 2) }} {{ abs($amount) < 0.005 ? '' : ($amount > 0 ? 'Dr' : 'Cr') }}
