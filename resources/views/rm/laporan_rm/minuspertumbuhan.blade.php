<td style="text-align: center;">{{ $sqloperasi->total }}</td> 
                                <td style="text-align: center;">
                                    @if($pertumbuhan_operasi >= 0) <i class="tf-icons bx bx-trending-up"style="color:rgb(9, 255, 0);">{{ $pertumbuhan_operasi }} %</i> @endif 
                                    @if($pertumbuhan_operasi < 0) <i class="tf-icons bx bx-trending-down"style="color:rgb(255, 0, 0);"> {{ $pertumbuhan_operasi }} %</i>@endif
                                </td>
                                <td style="text-align: center;">{{ $sqlirm->total }}</td>
                                <td style="text-align: center;">
                                    @if($pertumbuhan_irm >= 0) <i class="tf-icons bx bx-trending-up"style="color:rgb(9, 255, 0);">{{ $pertumbuhan_irm }} %</i> @endif 
                                    @if($pertumbuhan_irm < 0) <i class="tf-icons bx bx-trending-down"style="color:rgb(255, 0, 0);"> {{ $pertumbuhan_irm }} %</i>@endif
                                </td>