<table>
    <!-- thead dan tbody seperti sebelumnya -->
    
    <tfoot>
        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold;">Total</td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('laki') }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('perempuan') }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_1') > 0 ? collect($totalData)->sum('umur_lt_1') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_4') > 0 ? collect($totalData)->sum('umur_lt_4') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_9') > 0 ? collect($totalData)->sum('umur_lt_9') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_14') > 0 ? collect($totalData)->sum('umur_lt_14') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_19') > 0 ? collect($totalData)->sum('umur_lt_19') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_44') > 0 ? collect($totalData)->sum('umur_lt_44') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_54') > 0 ? collect($totalData)->sum('umur_lt_54') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_59') > 0 ? collect($totalData)->sum('umur_lt_59') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_69') > 0 ? collect($totalData)->sum('umur_lt_69') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_lt_70') > 0 ? collect($totalData)->sum('umur_lt_70') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('umur_null') > 0 ? collect($totalData)->sum('umur_null') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('meninggal_kurang_48jam') > 0 ? collect($totalData)->sum('meninggal_kurang_48jam') : '' }}
            </td>
            <td style="text-align: center; font-weight: bold;">
                {{ collect($totalData)->sum('meninggal_lebih_48jam') > 0 ? collect($totalData)->sum('meninggal_lebih_48jam') : '' }}
            </td>
        </tr>
    </tfoot>
</table>